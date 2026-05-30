<?php

namespace App\Traits;

use App\Observers\AuditObserver;

/**
 * Pasang trait ini di model yang perubahannya ingin dicatat audit.
 * Atur properti $auditExclude di model untuk men-skip atribut tertentu.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::observe(AuditObserver::class);
    }
}
