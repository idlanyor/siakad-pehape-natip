<?php
// tests/Unit/UjianModelTest.php
namespace Tests\Unit;

use App\Models\Ujian;
use Tests\TestCase;

class UjianModelTest extends TestCase
{
    public function test_status_accessors_for_published(): void
    {
        $ujian = new Ujian(['status' => 'published', 'is_published' => true]);
        $this->assertSame('card-success', $ujian->status_color);
        $this->assertSame('success', $ujian->status_badge);
        $this->assertSame('Tersedia', $ujian->status_label);
    }

    public function test_status_accessors_for_draft(): void
    {
        $ujian = new Ujian(['status' => 'draft', 'is_published' => false]);
        $this->assertSame('secondary', $ujian->status_badge);
        $this->assertSame('Belum dibuka', $ujian->status_label);
    }

    public function test_soals_and_sesis_relations_exist(): void
    {
        $ujian = new Ujian();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class, $ujian->soals());
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class, $ujian->sesis());
    }
}
