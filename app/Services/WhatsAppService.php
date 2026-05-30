<?php

namespace App\Services;

use App\Models\WaLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected bool $enabled;

    public function __construct()
    {
        $this->apiUrl = rtrim(config('services.whatsapp.api_url'), '/');
        $this->apiKey = config('services.whatsapp.api_key', '');
        $this->enabled = (bool) config('services.whatsapp.enabled', true);
    }

    /**
     * Kirim pesan teks via WhatsApp.
     */
    public function send(string $phone, string $message, ?string $event = null): bool
    {
        $phone = $this->normalizePhone($phone);

        if (!$this->enabled) {
            Log::info('[WA] Disabled — skip kirim ke ' . $phone);
            return false;
        }

        if (empty($this->apiKey)) {
            Log::warning('[WA] API key belum dikonfigurasi.');
            return false;
        }

        if (empty($phone)) {
            Log::warning('[WA] Nomor telepon kosong, skip.');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
            ])->post($this->apiUrl . '/send-message', [
                'to'      => $phone,
                'message' => $message,
            ]);

            $success = $response->successful();

            WaLog::create([
                'phone'    => $phone,
                'message'  => $message,
                'event'    => $event,
                'status'   => $success ? 'sent' : 'failed',
                'response' => $response->body(),
            ]);

            if (!$success) {
                Log::error('[WA] Gagal kirim ke ' . $phone . ': ' . $response->body());
            }

            return $success;
        } catch (\Throwable $e) {
            WaLog::create([
                'phone'    => $phone,
                'message'  => $message,
                'event'    => $event,
                'status'   => 'error',
                'response' => $e->getMessage(),
            ]);

            Log::error('[WA] Exception kirim ke ' . $phone . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Normalisasi nomor ke format 62xxx.
     */
    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '08')) {
            $phone = '62' . substr($phone, 1);
        }

        if (str_starts_with($phone, '+')) {
            $phone = ltrim($phone, '+');
        }

        return $phone;
    }
}
