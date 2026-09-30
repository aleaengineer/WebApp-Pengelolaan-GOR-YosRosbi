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
        $lapangan = Lapangan::first();
        if (!$lapangan) {
            return response()->json(['available' => false, 'reason' => 'Lapangan belum dikonfigurasi', 'suggestion' => null], 404);
        }
        $result = $service->checkAvailability($lapangan->id, $request->tanggal, $request->jam_mulai, $request->jam_selesai, $request->tipe_sewa ?? 'per_jam');
        return response()->json($result);
    }

    public function checkCoupon(Request $request, BookingService $service)
    {
        $request->validate(['code' => 'required|string', 'total_harga' => 'nullable|integer', 'tipe_sewa' => 'nullable|in:per_jam,harian']);
        $result = $service->validasiKupon(
            $request->code,
            $request->user(),
            $request->tipe_sewa ?? 'per_jam',
            (int) $request->total_harga,
            false // preview saja; cek bentrok kuota member dilakukan saat store
        );
        if ($result['error']) {
            return response()->json(['valid' => false, 'message' => $result['error']]);
        }
        $coupon = $result['coupon'];
        return response()->json([
            'valid'=>true,
            'message'=>'Kupon valid '.$coupon->value.'%'.($coupon->max_discount ? ' max Rp'.number_format($coupon->max_discount,0,',','.') : ''),
            'discount'=>$result['discount'],
            'coupon'=>['code'=>$coupon->code,'value'=>$coupon->value,'max_discount'=>$coupon->max_discount]
        ]);
    }
}
