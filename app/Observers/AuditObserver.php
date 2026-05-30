<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Mencatat perubahan model ber-trait Auditable ke tabel audit_logs.
 * Atribut sensitif/noise di-skip; nilai dipangkas agar JSON tidak bengkak.
 */
class AuditObserver
{
    /** Atribut yang tidak pernah dicatat (sensitif / noise). */
    protected array $globalHidden = [
        'password', 'remember_token', 'two_factor_secret',
        'two_factor_recovery_codes', 'updated_at', 'created_at',
    ];

    public function created(Model $model): void
    {
        $this->record($model, 'created', null, $this->filter($model, $model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $old = [];
        foreach (array_keys($changes) as $key) {
            $old[$key] = $model->getOriginal($key);
        }

        $this->record($model, 'updated', $this->filter($model, $old), $this->filter($model, $changes));
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted', $this->filter($model, $model->getOriginal()), null);
    }

    protected function record(Model $model, string $action, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'user_id'    => auth()->id(),
            'user_name'  => auth()->user()?->name ?? 'Sistem',
            'action'     => $action,
            'model'      => class_basename($model),
            'model_id'   => $model->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
        ]);
    }

    /**
     * Buang atribut sensitif/noise. Jika model mendefinisikan
     * $auditExclude, itu juga di-skip.
     */
    protected function filter(Model $model, array $attributes): array
    {
        $hidden = array_merge($this->globalHidden, $model->auditExclude ?? []);

        return collect($attributes)
            ->except($hidden)
            ->map(fn($v) => is_string($v) ? mb_strimwidth($v, 0, 500, '…') : $v)
            ->toArray();
    }
}
