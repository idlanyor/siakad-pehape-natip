<?php

namespace Tests\Feature;

use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengaturanUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::factory()->create(['role' => 0]);
        Pengaturan::create(['key' => 'logo', 'label' => 'Logo', 'grup' => 'media', 'tipe' => 'image', 'value' => null]);
    }

    public function test_logo_gambar_tersimpan(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.cms.pengaturan.update'), ['logo' => UploadedFile::fake()->image('logo.png')])
            ->assertSessionHasNoErrors();

        Storage::disk('public')->assertExists(Pengaturan::where('key', 'logo')->value('value'));
    }

    /** Regresi: sebelumnya file apa pun tersimpan ke disk public tanpa validasi. */
    public function test_file_bukan_gambar_ditolak(): void
    {
        foreach ([
            UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;'),
            UploadedFile::fake()->createWithContent('x.html', '<script>alert(1)</script>'),
            UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        ] as $file) {
            $this->actingAs($this->admin)
                ->put(route('admin.cms.pengaturan.update'), ['logo' => $file])
                ->assertSessionHasErrors('logo');
        }

        $this->assertNull(Pengaturan::where('key', 'logo')->value('value'));
        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
