<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnnouncementEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ujianId;
    public $message;
    public $sender;
    public $timestamp;

    /**
     * Create a new event instance.
     *
     * @param int $ujianId ID ujian
     * @param string $message Pesan pengumuman
     * @param string $sender Nama pengirim
     */
    public function __construct(int $ujianId, string $message, string $sender)
    {
        $this->ujianId = $ujianId;
        $this->message = $message;
        $this->sender = $sender;
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
            new PrivateChannel('proktor.ujian.' . $this->ujianId),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'Pengumuman';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'ujian_id' => $this->ujianId,
            'message' => $this->message,
            'sender' => $this->sender,
            'timestamp' => $this->timestamp,
        ];
    }
}
