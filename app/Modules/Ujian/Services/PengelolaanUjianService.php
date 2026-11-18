<?php

namespace App\Modules\Ujian\Services;

use App\Modules\Ujian\Exceptions\UjianException;
use App\Modules\Ujian\Models\BankSoal;
use App\Modules\Ujian\Models\SoalUjian;
use App\Modules\Ujian\Models\Ujian;
use App\Services\ELearningNotifier;
use Illuminate\Support\Facades\DB;

/**
 * Aturan pengelolaan ujian oleh guru/admin: status publikasi dan penyusunan soal.
 *
 * Soal dikunci begitu ada sesi siswa, karena lembar jawaban siswa merujuk
 * langsung ke baris soal_ujian.
 */
class PengelolaanUjianService
{
    public function publikasikan(Ujian $ujian): void
    {
        $soal = $ujian->soal()->get();

        if ($soal->isEmpty()) {
            throw UjianException::belumAdaSoal();
        }

        $tanpaKunci = $soal
            ->filter(fn (SoalUjian $s) => $s->isPilihanGanda() && ! array_key_exists((string) $s->kunci_jawaban, $s->opsi()))
            ->pluck('nomor')
            ->all();

        if ($tanpaKunci !== []) {
            throw UjianException::soalTanpaKunci($tanpaKunci);
        }

        $ujian->update(['status' => Ujian::STATUS_PUBLISHED]);
        ELearningNotifier::ujianBaru($ujian);
    }

    public function kembalikanKeDraft(Ujian $ujian): void
    {
        if ($ujian->sesi()->exists()) {
            throw UjianException::sudahDikerjakan();
        }

        $ujian->update(['status' => Ujian::STATUS_DRAFT]);
    }

    public function tandaiSelesai(Ujian $ujian): void
    {
        $ujian->update(['status' => Ujian::STATUS_SELESAI]);
    }

    public function pastikanSoalBisaDiubah(Ujian $ujian): void
    {
        if ($ujian->sesi()->exists()) {
            throw UjianException::soalTerkunci();
        }
    }

    public function tambahSoal(Ujian $ujian, array $atribut): SoalUjian
    {
        $this->pastikanSoalBisaDiubah($ujian);

        return $ujian->soal()->create([...$atribut, 'nomor' => $this->nomorBerikutnya($ujian)]);
    }

    public function ubahSoal(SoalUjian $soal, array $atribut): SoalUjian
    {
        $this->pastikanSoalBisaDiubah($soal->ujian);
        $soal->update($atribut);

        return $soal;
    }

    public function hapusSoal(SoalUjian $soal): void
    {
        $ujian = $soal->ujian;
        $this->pastikanSoalBisaDiubah($ujian);

        DB::transaction(function () use ($soal, $ujian) {
            $soal->delete();
            $this->nomoriUlang($ujian, $ujian->soal()->pluck('id')->all());
        });
    }

    /**
     * Salin soal dari bank soal (yang belum terpasang) ke akhir daftar soal ujian.
     *
     * @param  list<int>  $bankSoalIds
     * @return int jumlah soal yang ditambahkan
     */
    public function tambahDariBank(Ujian $ujian, array $bankSoalIds): int
    {
        $this->pastikanSoalBisaDiubah($ujian);

        return DB::transaction(function () use ($ujian, $bankSoalIds) {
            $sudahAda = $ujian->soal()->whereNotNull('bank_soal_id')->pluck('bank_soal_id')->all();
            $nomor = $this->nomorBerikutnya($ujian);
            $jumlah = 0;

            BankSoal::query()
                ->whereKey($bankSoalIds)
                ->whereKeyNot($sudahAda)
                ->orderBy('id')
                ->each(function (BankSoal $bank) use ($ujian, &$nomor, &$jumlah) {
                    $ujian->soal()->create(['nomor' => $nomor++, ...SoalUjian::atributDariBankSoal($bank)]);
                    $jumlah++;
                });

            return $jumlah;
        });
    }

    /**
     * @param  list<int>  $soalIds  seluruh id soal ujian dalam urutan baru
     */
    public function urutkanSoal(Ujian $ujian, array $soalIds): void
    {
        $this->pastikanSoalBisaDiubah($ujian);

        $milikUjian = $ujian->soal()->pluck('id')->sort()->values()->all();
        $diminta = collect($soalIds)->map(fn ($id) => (int) $id);

        if ($diminta->sort()->values()->all() !== $milikUjian) {
            throw new UjianException('Urutan soal tidak lengkap atau memuat soal dari ujian lain.');
        }

        DB::transaction(fn () => $this->nomoriUlang($ujian, $diminta->all()));
    }

    private function nomorBerikutnya(Ujian $ujian): int
    {
        return (int) $ujian->soal()->max('nomor') + 1;
    }

    private function nomoriUlang(Ujian $ujian, array $soalIds): void
    {
        foreach (array_values($soalIds) as $i => $id) {
            $ujian->soal()->whereKey($id)->update(['nomor' => $i + 1]);
        }
    }
}
