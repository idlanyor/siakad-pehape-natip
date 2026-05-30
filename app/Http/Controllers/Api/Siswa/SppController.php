<?php

namespace App\Http\Controllers\Api\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagihanSppResource;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use App\Services\MidtransService;

class SppController extends Controller
{
    public function index()
    {
        $siswa = Siswa::where('user_id', auth()->id())->firstOrFail();

        $tagihans = TagihanSpp::where('siswa_id', $siswa->id)
            ->orderBy('bulan', 'desc')
            ->get();

        return TagihanSppResource::collection($tagihans);
    }

    public function show(TagihanSpp $tagihan, MidtransService $midtrans)
    {
        $siswa = Siswa::where('user_id', auth()->id())->firstOrFail();
        abort_if($tagihan->siswa_id !== $siswa->id, 403, 'Akses ditolak.');

        $tagihan->load('pembayarans');

        $data = (new TagihanSppResource($tagihan))->toArray(request());

        // Generate Midtrans snap token if not yet paid
        if ($tagihan->status !== 'lunas' && $tagihan->sisa_tagihan > 0 && config('midtrans.server_key')) {
            try {
                $snap = $midtrans->createSnapToken($tagihan);
                $data['snap_token'] = $snap['snap_token'];
                $data['order_id']   = $snap['order_id'];
            } catch (\Exception $e) {
                $data['snap_token'] = null;
                $data['snap_error'] = $e->getMessage();
            }
        }

        return response()->json(['data' => $data]);
    }
}
