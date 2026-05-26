<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftar_ppdbs', function (Blueprint $table) {
            $table->id();
            $table->string('no_pendaftaran', 20)->unique();

            // Data pribadi
            $table->string('nama');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir', 100);
            $table->date('tanggal_lahir');
            $table->string('nisn', 20)->nullable();
            $table->text('alamat');
            $table->string('email')->nullable();
            $table->string('no_hp', 20);
            $table->string('asal_sekolah');

            // Orang tua / wali
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('no_hp_ortu', 20)->nullable();
            $table->string('pekerjaan_ortu', 100)->nullable();

            // Akademik
            $table->foreignId('jurusan_pilihan_id')->constrained('jurusans')->restrictOnDelete();
            $table->foreignId('jurusan_pilihan_2_id')->nullable()->constrained('jurusans')->nullOnDelete();
            $table->decimal('nilai_rata_rata', 5, 2)->nullable()->comment('Rata-rata rapor SMP');

            // Dokumen (path files di storage)
            $table->string('foto')->nullable();
            $table->string('dok_ijazah')->nullable();
            $table->string('dok_rapor')->nullable();
            $table->string('dok_kk')->nullable();
            $table->string('dok_akta')->nullable();

            // Status seleksi
            $table->enum('status', ['pending', 'verifikasi', 'diterima', 'ditolak'])->default('pending');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('kelas_diterima_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diproses_at')->nullable();

            // Periode
            $table->smallInteger('gelombang')->default(1);
            $table->string('tahun_ajaran', 10)->default('2026/2027');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftar_ppdbs');
    }
};
