<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use App\Models\HargaSewa;
use App\Models\PaketMember;
use App\Models\Setting;
use App\Services\BookingService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $lapangan = Lapangan::first();
            $hargaPerJam = HargaSewa::where('tipe','per_jam')->first();
            $hargaHarian = HargaSewa::where('tipe','harian')->first();
            $pakets = PaketMember::where('is_active', true)->get();
            $tanggal = $request->get('tanggal', date('Y-m-d'));
            $service = new BookingService();
            $fixedSlots = $service->generateFixedSlots($tanggal);
            $bookings = \App\Models\Booking::with('user')->whereDate('tanggal', $tanggal)->whereIn('status', \App\Models\Booking::STATUS_ACTIVE)->get();
            $blokirs = \App\Models\BlokirJadwal::whereDate('tanggal', $tanggal)->get();
        } catch (\Throwable $e) {
            $lapangan = null;
            $hargaPerJam = null;
            $hargaHarian = null;
            $pakets = collect();
            $tanggal = $request->get('tanggal', date('Y-m-d'));
            $fixedSlots = (new BookingService())->generateFixedSlots($tanggal);
            $bookings = collect();
            $blokirs = collect();
        }
        return view('landing', compact('lapangan','hargaPerJam','hargaHarian','pakets','fixedSlots','bookings','blokirs','tanggal'));
    }

    public function jadwal(Request $request)
    {
        try {
            $tanggal = $request->get('tanggal', date('Y-m-d'));
            $service = new BookingService();
            $fixedSlots = $service->generateFixedSlots($tanggal);
            $bookings = \App\Models\Booking::whereDate('tanggal', $tanggal)->whereIn('status', \App\Models\Booking::STATUS_ACTIVE)->get();
            $blokirs = \App\Models\BlokirJadwal::whereDate('tanggal', $tanggal)->get();
        } catch (\Throwable $e) {
            $tanggal = $request->get('tanggal', date('Y-m-d'));
            $fixedSlots = (new BookingService())->generateFixedSlots($tanggal);
            $bookings = collect();
            $blokirs = collect();
        }
        return view('jadwal', compact('fixedSlots','bookings','blokirs','tanggal'));
    }

    public function checkAvailability(Request $request, BookingService $service)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'tipe_sewa' => 'nullable|in:per_jam,harian'
        ]);
        $lapanganId = \App\Models\Lapangan::first()->id ?? 1;
        $result = $service->checkAvailability($lapanganId, $request->tanggal, $request->jam_mulai, $request->jam_selesai, $request->tipe_sewa ?? 'per_jam');
        return response()->json($result);
    }

    public function checkCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string', 'total_harga' => 'nullable|integer', 'tipe_sewa' => 'nullable|in:per_jam,harian']);
        $code = strtoupper(trim($request->code));
        $coupon = \App\Models\Coupon::where('code', $code)->first();
        if (!$coupon) return response()->json(['valid'=>false,'message'=>'Kode kupon tidak ditemukan']);
        if (!$coupon->is_active) return response()->json(['valid'=>false,'message'=>'Kupon tidak aktif']);
        if ($coupon->isExpired()) return response()->json(['valid'=>false,'message'=>'Kupon sudah expired']);
        if ($coupon->quota !== null && $coupon->used_count >= $coupon->quota) return response()->json(['valid'=>false,'message'=>'Kuota kupon habis']);
        if ($request->tipe_sewa && $coupon->tipe_sewa !== 'per_jam') {
            // only per_jam coupons
        }
        if ($request->tipe_sewa === 'harian') return response()->json(['valid'=>false,'message'=>'Kupon hanya untuk per_jam']);
        if (auth()->check() && !$coupon->canBeUsedBy(auth()->id())) {
            return response()->json(['valid'=>false,'message'=>'Kupon sudah dipakai maksimal '.$coupon->per_user_limit.'x']);
        }
        // Cek kuota member gratis
        if (auth()->check() && auth()->user()->member_package_id) {
            $paket = \App\Models\PaketMember::find(auth()->user()->member_package_id);
            if ($paket && auth()->user()->member_expired_at && \Carbon\Carbon::parse(auth()->user()->member_expired_at)->isFuture()) {
                // Jika masih ada sisa kuota, informasikan tidak bisa stack
                // Tidak block di preview, hanya info
            }
        }
        $total = (int) $request->total_harga;
        if ($total && $coupon->min_amount && $total < $coupon->min_amount) {
            return response()->json(['valid'=>false,'message'=>'Minimal belanja Rp'.number_format($coupon->min_amount,0,',','.')]);
        }
        $discount = $total ? $coupon->calculateDiscount($total) : 0;
        return response()->json([
            'valid'=>true,
            'message'=>'Kupon valid '.$coupon->value.'%'.($coupon->max_discount ? ' max Rp'.number_format($coupon->max_discount,0,',','.') : ''),
            'discount'=>$discount,
            'coupon'=>['code'=>$coupon->code,'value'=>$coupon->value,'max_discount'=>$coupon->max_discount]
        ]);
    }
}
