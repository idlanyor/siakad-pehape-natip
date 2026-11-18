<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Skema modul Ujian Online (hasil squash 24 migration ujian-service).
 *
 * Perbedaan utama dari skema ujian-service:
 * - Kolom snapshot SIAKAD (siswa_nama, siswa_nis, mapel_nama, kelas_nama,
 *   guru_nama) diganti FK nyata ke tabel SIAKAD.
 * - periode_ujian memakai semester_id (FK semesters) alih-alih string
 *   tahun_ajaran + enum semester.
 * - Kolom kembar "legacy" dibuang: ujian.is_published (cukup status),
 *   sesi_ujian.nilai (cukup nilai_akhir), sesi_ujian.violation_count
 *   (digabung jadi jumlah_pelanggaran), jawaban_siswa.soal_id dan .jawaban.
 * - Kolom turunan dibuang: periode_ujian.fase_aktif, ujian.jumlah_soal,
 *   ujian.jumlah_peserta (dihitung dari relasi).
 * - Dua tabel event (proktor_event dan proktor_events) disatukan.
 * - Tabel tak terpakai tidak ikut: media_soal, activity_log, users, siswas,
 *   user_siswa, admin_role*, admin_permission.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_ujian', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // "UAS Ganjil 2026/2027"
            $table->foreignId('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->enum('status', ['draft', 'aktif', 'selesai'])->default('draft');
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('fase_periode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periode_ujian')->cascadeOnDelete();
            $table->enum('tipe', ['input_soal', 'try_out', 'pelaksanaan', 'remedial', 'rekapitulasi']);
            $table->unsignedTinyInteger('urutan');
            $table->dateTime('tgl_mulai');
            $table->dateTime('tgl_selesai');
            $table->enum('override_status', ['paksa_buka', 'paksa_tutup'])->nullable();
            $table->foreignId('override_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('override_at')->nullable();
            $table->timestamps();

            $table->unique(['periode_id', 'tipe']);
            $table->index(['periode_id', 'urutan']);
            $table->index(['tgl_mulai', 'tgl_selesai']);
        });

        Schema::create('bank_soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mapel_id')->nullable()->constrained('mapels')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('topik')->nullable();
            $table->enum('tingkat_kesulitan', ['mudah', 'sedang', 'sulit'])->default('sedang');
            $table->json('tag')->nullable();
            $table->enum('tipe', ['pilihan_ganda', 'essay'])->default('pilihan_ganda');
            $table->text('pertanyaan');
            $table->text('opsi_a')->nullable();
            $table->text('opsi_b')->nullable();
            $table->text('opsi_c')->nullable();
            $table->text('opsi_d')->nullable();
            $table->text('opsi_e')->nullable();
            $table->char('kunci_jawaban', 1)->nullable(); // A–E, khusus pilihan ganda
            $table->decimal('bobot_default', 5, 2)->default(1);
            $table->timestamps();

            $table->index('tingkat_kesulitan');
            $table->index('tipe');
        });

        Schema::create('ujian', function (Blueprint $table) {
            $table->id();
            // restrict: menghapus periode tidak boleh diam-diam menghapus hasil ujian.
            $table->foreignId('periode_id')->constrained('periode_ujian')->restrictOnDelete();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->enum('tipe', ['quiz', 'uts', 'uas', 'remidi'])->default('quiz');
            $table->foreignId('jadwal_id')->nullable()->constrained('jadwals')->nullOnDelete();
            $table->foreignId('mapel_id')->constrained('mapels')->restrictOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->restrictOnDelete();
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai');
            $table->unsignedInteger('durasi_menit');
            $table->decimal('passing_grade', 5, 2)->default(0);
            $table->boolean('acak_soal')->default(false);
            $table->boolean('acak_opsi')->default(false);
            $table->boolean('tampilkan_nilai')->default(true);
            $table->enum('status', ['draft', 'published', 'selesai'])->default('draft');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['periode_id', 'status']);
            $table->index(['kelas_id', 'status']);
            $table->index(['waktu_mulai', 'waktu_selesai']);
        });

        Schema::create('soal_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
            $table->foreignId('bank_soal_id')->nullable()->constrained('bank_soal')->nullOnDelete();
            $table->unsignedInteger('nomor');
            $table->enum('tipe', ['pilihan_ganda', 'essay'])->default('pilihan_ganda');
            $table->text('pertanyaan');
            $table->text('opsi_a')->nullable();
            $table->text('opsi_b')->nullable();
            $table->text('opsi_c')->nullable();
            $table->text('opsi_d')->nullable();
            $table->text('opsi_e')->nullable();
            $table->char('kunci_jawaban', 1)->nullable();
            $table->decimal('bobot_nilai', 5, 2)->default(1);
            $table->timestamps();

            $table->index(['ujian_id', 'nomor']);
        });

        Schema::create('sesi_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            // Kelas siswa saat ujian dikerjakan (siswa bisa naik kelas setelahnya).
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->enum('status', ['belum', 'berlangsung', 'selesai', 'timeout', 'diskualifikasi'])->default('belum');
            $table->boolean('is_resmi')->default(true); // false untuk try out
            $table->enum('fase', ['try_out', 'pelaksanaan', 'remedial'])->default('pelaksanaan');
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->unsignedInteger('durasi_detik')->default(0);
            $table->decimal('nilai_otomatis', 5, 2)->nullable(); // koreksi otomatis PG
            $table->decimal('nilai_manual', 5, 2)->nullable();   // koreksi manual essay
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->string('predikat', 2)->nullable();
            $table->unsignedInteger('jumlah_pelanggaran')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->unique(['ujian_id', 'siswa_id']);
            $table->index(['ujian_id', 'status']);
            $table->index(['siswa_id', 'is_resmi']);
        });

        Schema::create('jawaban_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_ujian_id')->constrained('sesi_ujian')->cascadeOnDelete();
            $table->foreignId('soal_ujian_id')->constrained('soal_ujian')->cascadeOnDelete();
            $table->char('jawaban_pilihan', 1)->nullable(); // A–E untuk pilihan ganda
            $table->text('jawaban_text')->nullable();       // essay
            $table->boolean('is_ragu')->default(false);
            $table->boolean('is_benar')->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['sesi_ujian_id', 'soal_ujian_id']);
        });

        Schema::create('proktor_event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_ujian_id')->constrained('sesi_ujian')->cascadeOnDelete();
            // Pelanggaran (tab_switch, blur, copy, ...) atau aksi proktor
            // (force_finish, disqualify, reset, announce). Divalidasi di aplikasi.
            $table->string('tipe', 50);
            $table->text('keterangan')->nullable();
            $table->json('detail')->nullable();
            // Pelaku aksi proktor; null bila dilaporkan otomatis dari browser siswa.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['sesi_ujian_id', 'created_at']);
            $table->index(['sesi_ujian_id', 'tipe']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proktor_event');
        Schema::dropIfExists('jawaban_siswa');
        Schema::dropIfExists('sesi_ujian');
        Schema::dropIfExists('soal_ujian');
        Schema::dropIfExists('ujian');
        Schema::dropIfExists('bank_soal');
        Schema::dropIfExists('fase_periode');
        Schema::dropIfExists('periode_ujian');
    }
};
