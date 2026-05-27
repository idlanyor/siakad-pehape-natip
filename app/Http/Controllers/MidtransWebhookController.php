<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\PembayaranSpp;
use App\Models\TagihanSpp;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request, MidtransService $midtrans)
    {
        $payload = $request->all();

        Log::info('Midtrans webhook received', ['order_id' => $payload['order_id'] ?? null]);

        // 1. Verifikasi signature
        $isValid = $midtrans->verifySignature(
            $payload['order_id']     ?? '',
            $payload['status_code']  ?? '',
            $payload['gross_amount'] ?? '',
            $payload['signature_key'] ?? ''
        );

        if (! $isValid) {
            Log::warning('Midtrans webhook: invalid signature', $payload);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // 2. Parse order ID: format SPP-{tagihan_id}-{timestamp}
        $orderId = $payload['order_id'] ?? '';
        if (! preg_match('/^SPP-(\d+)-\d+$/', $orderId, $matches)) {
            Log::warning('Midtrans webhook: unrecognized order_id format', ['order_id' => $orderId]);
            return response()->json(['message' => 'Unknown order format'], 200);
        }

        $tagihanId        = (int) $matches[1];
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus      = $payload['fraud_status'] ?? 'accept';
        $grossAmount      = (float) $payload['gross_amount'];
        $paymentType      = $payload['payment_type'] ?? null;
        $transactionId    = $payload['transaction_id'] ?? null;

        // 3. Cek tagihan
        $tagihan = TagihanSpp::with('siswa.user')->find($tagihanId);
        if (! $tagihan) {
            Log::error('Midtrans webhook: tagihan not found', ['tagihan_id' => $tagihanId]);
            return response()->json(['message' => 'Tagihan not found'], 200);
        }

        // 4. Proses hanya jika status berhasil & fraud aman
        $isSuccess = in_array($transactionStatus, ['capture', 'settlement']) && $fraudStatus !== 'deny';
        $isPending = $transactionStatus === 'pending';
        $isFailed  = in_array($transactionStatus, ['deny', 'expire', 'cancel']);

        if ($isSuccess) {
            DB::transaction(function () use ($tagihan, $orderId, $transactionId, $paymentType, $grossAmount) {
                // Cek jangan sampai double-record untuk order_id yang sama
                $alreadyRecorded = PembayaranSpp::where('midtrans_order_id', $orderId)->exists();
                if ($alreadyRecorded) {
                    Log::info('Midtrans webhook: already recorded, skip', ['order_id' => $orderId]);
                    return;
                }

                // Simpan pembayaran otomatis (sudah dikonfirmasi)
                PembayaranSpp::create([
                    'tagihan_spp_id'        => $tagihan->id,
                    'tanggal_bayar'         => now()->toDateString(),
                    'nominal_bayar'         => $grossAmount,
                    'metode'                => $this->mapPaymentType($paymentType),
                    'catatan'               => 'Dibayar via Midtrans (' . $paymentType . ')',
                    'dicatat_oleh'          => null,
                    'status_konfirmasi'     => 'confirmed',
                    'dikonfirmasi_at'       => now(),
                    'midtrans_order_id'     => $orderId,
                    'midtrans_transaction_id' => $transactionId,
                    'payment_type'          => $paymentType,
                ]);

                // Update status tagihan
                $tagihan->recalculateStatus();

                // Notifikasi ke siswa
                $siswaUser = $tagihan->siswa?->user;
                if ($siswaUser) {
                    Notifikasi::create([
                        'user_id' => $siswaUser->id,
                        'judul'   => 'Pembayaran SPP Berhasil',
                        'pesan'   => 'Pembayaran SPP ' . $tagihan->bulan_label .
                                     ' sebesar Rp ' . number_format($grossAmount, 0, ',', '.') .
                                     ' telah berhasil dikonfirmasi otomatis.',
                        'url'     => route('siswa.spp.show', $tagihan->id),
                    ]);
                }

                // Notifikasi ke semua admin
                User::where('role', 0)->each(function (User $admin) use ($tagihan, $grossAmount) {
                    Notifikasi::create([
                        'user_id' => $admin->id,
                        'judul'   => 'Pembayaran SPP Masuk (Midtrans)',
                        'pesan'   => $tagihan->siswa->nama . ' membayar SPP ' .
                                     $tagihan->bulan_label . ' sebesar Rp ' .
                                     number_format($grossAmount, 0, ',', '.') . ' via Midtrans.',
                        'url'     => route('admin.keuangan.kwitansi', 0), // fallback, bisa diperbarui
                    ]);
                });
            });

            Log::info('Midtrans webhook: payment success recorded', ['order_id' => $orderId]);
        }

        if ($isFailed) {
            Log::info('Midtrans webhook: payment failed/expired', [
                'order_id' => $orderId,
                'status'   => $transactionStatus,
            ]);
        }

        return response()->json(['message' => 'OK'], 200);
    }

    /**
     * Map Midtrans payment_type ke label metode yang dimengerti sistem.
     */
    private function mapPaymentType(?string $type): string
    {
        return match (true) {
            str_contains($type ?? '', '_va')  => 'transfer',
            str_contains($type ?? '', 'bank_transfer') => 'transfer',
            $type === 'qris'                  => 'qris',
            $type === 'gopay'                 => 'qris',
            $type === 'shopeepay'             => 'qris',
            default                           => 'transfer',
        };
    }
}
