<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_membuat_user_tercatat_di_audit_log(): void
    {
        $actor = User::factory()->create();
        $this->actingAs($actor);

        $baru = User::factory()->create(['name' => 'Murid Baru']);

        $log = AuditLog::where('model', 'User')->where('model_id', $baru->id)->where('action', 'created')->first();

        $this->assertNotNull($log);
        $this->assertSame($actor->id, $log->user_id);
    }

    public function test_update_mencatat_old_dan_new_values(): void
    {
        $actor = User::factory()->create();
        $this->actingAs($actor);

        $target = User::factory()->create(['name' => 'Nama Lama']);
        $target->update(['name' => 'Nama Baru']);

        $log = AuditLog::where('model', 'User')->where('model_id', $target->id)->where('action', 'updated')->latest()->first();

        $this->assertNotNull($log);
        $this->assertSame('Nama Lama', $log->old_values['name']);
        $this->assertSame('Nama Baru', $log->new_values['name']);
    }

    public function test_password_tidak_pernah_tercatat(): void
    {
        $this->actingAs(User::factory()->create());

        $target = User::factory()->create();
        $target->update(['password' => bcrypt('rahasia-banget')]);

        $logs = AuditLog::where('model', 'User')->where('model_id', $target->id)->get();

        foreach ($logs as $log) {
            $this->assertArrayNotHasKey('password', $log->new_values ?? []);
            $this->assertArrayNotHasKey('password', $log->old_values ?? []);
        }
    }

    public function test_hapus_tercatat_sebagai_deleted(): void
    {
        $this->actingAs(User::factory()->create());

        $target = User::factory()->create();
        $id = $target->id;
        $target->delete();

        $this->assertDatabaseHas('audit_logs', [
            'model'    => 'User',
            'model_id' => $id,
            'action'   => 'deleted',
        ]);
    }

    public function test_admin_bisa_lihat_halaman_audit_log(): void
    {
        $admin = User::factory()->create(['role' => 0]);

        $this->actingAs($admin)->get(route('admin.audit-log.index'))->assertOk();
    }
}
