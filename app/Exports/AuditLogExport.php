<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AuditLogExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private Collection $logs) {}

    public function title(): string
    {
        return 'Audit Log';
    }

    public function headings(): array
    {
        return ['Waktu', 'Pengguna', 'Aksi', 'Model', 'ID', 'Perubahan', 'IP'];
    }

    public function array(): array
    {
        return $this->logs->map(function ($log) {
            $changes = $log->action === 'updated'
                ? collect($log->new_values ?? [])->map(fn($v, $k) => "$k: " . ($log->old_values[$k] ?? '—') . " → $v")->implode('; ')
                : collect($log->new_values ?? $log->old_values ?? [])->map(fn($v, $k) => "$k: $v")->implode('; ');

            return [
                $log->created_at->format('d/m/Y H:i:s'),
                $log->user_name,
                $log->action_label,
                $log->model,
                $log->model_id,
                $changes,
                $log->ip_address,
            ];
        })->toArray();
    }
}
