<?php

namespace App\Services;

use App\Models\TagihanSpp;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    /**
     * Generate Snap token untuk pembayaran tagihan SPP.
     * Order ID bersifat unik per percobaan bayar: spp-{tagihan_id}-{timestamp}
     */
    public function createSnapToken(TagihanSpp $tagihan): array
    {
        $tagihan->load('siswa.user');
        $siswa = $tagihan->siswa;

        $orderId = 'SPP-' . $tagihan->id . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) $tagihan->sisa_tagihan,
            ],
            'customer_details' => [
                'first_name' => $siswa->nama,
                'email'      => $siswa->user?->email ?? ($siswa->nisn . '@siswa.sch.id'),
                'phone'      => $siswa->no_hp ?? '',
            ],
            'item_details' => [
                [
                    'id'       => 'SPP-' . $tagihan->id,
                    'price'    => (int) $tagihan->sisa_tagihan,
                    'quantity' => 1,
                    'name'     => 'SPP ' . $tagihan->bulan_label . ' - ' . $siswa->nama,
                ],
            ],
            'callbacks' => [
                'finish' => route('siswa.spp.show', $tagihan),
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return [
            'snap_token' => $snapToken,
            'order_id'   => $orderId,
        ];
    }

    /**
     * Verifikasi signature key dari notifikasi webhook Midtrans.
     * Format: SHA512(order_id + status_code + gross_amount + server_key)
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $incomingSignature): bool
    {
        $serverKey = config('midtrans.server_key');
        $expected  = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        return hash_equals($expected, $incomingSignature);
    }
}
