<?php

namespace App\Events;

use App\Models\SesiUjian;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SesiUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sesi;
    public $ujianId;

    /**
     * Create a new event instance.
     *
     * @param SesiUjian $sesi
     */
    public function __construct(SesiUjian $sesi)
    {
        $this->sesi = [
            'id' => $sesi->id,
            'siswa_id' => $sesi->siswa_id,
            'siswa_nama' => $sesi->siswa_nama,
            'siswa_nis' => $sesi->siswa_nis,
            'status' => $sesi->status,
            'waktu_mulai' => $sesi->waktu_mulai,
            'waktu_selesai' => $sesi->waktu_selesai,
            'tab_switch_count' => $sesi->tab_switch_count,
            'nilai_akhir' => $sesi->nilai_akhir,
        ];
        $this->ujianId = $sesi->ujian_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('proktor.ujian.' . $this->ujianId),
            new PrivateChannel('sesi.' . $this->sesi['id']),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'SesiUpdated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'sesi' => $this->sesi,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
