<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProktorEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sesiUjianId;
    public $siswaId;
    public $siswaNama;
    public $eventType;
    public $eventData;
    public $timestamp;

    /**
     * Create a new event instance.
     *
     * @param int $sesiUjianId ID sesi ujian
     * @param int $siswaId ID siswa
     * @param string $siswaNama Nama siswa
     * @param string $eventType Tipe event (tab_switch, blur, copy, paste, etc)
     * @param array $eventData Data tambahan event
     */
    public function __construct(
        int $sesiUjianId,
        int $siswaId,
        string $siswaNama,
        string $eventType,
        array $eventData = []
    ) {
        $this->sesiUjianId = $sesiUjianId;
        $this->siswaId = $siswaId;
        $this->siswaNama = $siswaNama;
        $this->eventType = $eventType;
        $this->eventData = $eventData;
        $this->timestamp = now()->toIso8601String();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('proktor.sesi.' . $this->sesiUjianId),
            new PrivateChannel('proktor.ujian.' . ($this->eventData['ujian_id'] ?? 0)),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'proktor.event';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'sesi_ujian_id' => $this->sesiUjianId,
            'siswa_id' => $this->siswaId,
            'siswa_nama' => $this->siswaNama,
            'event_type' => $this->eventType,
            'event_data' => $this->eventData,
            'timestamp' => $this->timestamp,
            'severity' => $this->getSeverity(),
        ];
    }

    /**
     * Get severity level untuk event
     */
    private function getSeverity(): string
    {
        return match($this->eventType) {
            'tab_switch', 'blur', 'fullscreen_exit' => 'high',
            'copy', 'paste', 'right_click' => 'medium',
            'reconnect', 'focus' => 'low',
            'devtools_open' => 'critical',
            default => 'low',
        };
    }
}