<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Lapangan;
use App\Models\HargaSewa;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['lapangan','payment'])->where('user_id', auth()->id())->latest()->paginate(10);
        return view('booking.index', compact('bookings'));
    }

    public function create(Request $request)
    {
        $tanggal = $request->get('tanggal', date('Y-m-d'));
        $service = new BookingService();
        $fixedSlots = $service->generateFixedSlots($tanggal);
        $hargaPerJam = HargaSewa::where('tipe','per_jam')->first();
        $hargaHarian = HargaSewa::where('tipe','harian')->first();
        return view('booking.create', compact('fixedSlots','tanggal','hargaPerJam','hargaHarian'));
    }

    public function store(Request $request, BookingService $service)
    {
        $request->validate([
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'jenis_kegiatan' => 'required|in:badminton,voly,basket,event_lain',
            'tipe_sewa' => 'required|in:per_jam,harian',
            'metode' => 'required|in:transfer,midtrans,cash',
            'catatan' => 'nullable|string|max:500',
            'coupon_code' => 'nullable|string|max:20',
        ]);

        $lapangan = Lapangan::first();
        if (!$lapangan) {
            return back()->withErrors(['lapangan' => 'Lapangan belum dikonfigurasi']);
        }

        // Validasi jam operasional
        $jamMulai = $request->jam_mulai;
        $jamSelesai = $request->jam_selesai;
        if ($jamSelesai === '00:00') $jamSelesai = '00:00';

        // Cek availability + buffer 30
        $check = $service->checkAvailability($lapangan->id, $request->tanggal, $jamMulai, $jamSelesai, $request->tipe_sewa);
        if (!$check['available']) {
            $msg = $check['reason'];
            if ($check['suggestion']) $msg .= ". Saran slot tersedia: {$check['suggestion']}";
            return back()->withErrors(['jam_mulai' => $msg])->withInput();
        }

        // Hitung harga
        $hargaRow = HargaSewa::where('tipe', $request->tipe_sewa)->first();
        $totalHarga = 0;
        $durasi = 1;
        $isGratisMember = false;
        if ($request->tipe_sewa === 'harian') {
            $totalHarga = $hargaRow ? $hargaRow->harga : 1500000;
            $durasi = 16;
        } else {
            $start = Carbon::parse($jamMulai);
            $end = Carbon::parse($jamSelesai);
            if ($end->lessThanOrEqualTo($start)) $end->addDay();
            $durasi = $start->diffInMinutes($end) / 60;
            $hargaPerJam = $hargaRow ? $hargaRow->harga : 50000;
            // Cek harga member
            if (auth()->user()->member_package_id && auth()->user()->member_expired_at && Carbon::parse(auth()->user()->member_expired_at)->isFuture()) {
                $hargaPerJam = $hargaRow->harga_member ?? $hargaPerJam;
            }
            $totalHarga = (int) ceil($durasi) * $hargaPerJam;
            // Jika pakai kuota member, total 0 jika kuota cukup
            if (auth()->user()->member_package_id) {
                $paketMember = \App\Models\PaketMember::find(auth()->user()->member_package_id);
                if ($paketMember) {
                    $usedJam = Booking::where('user_id', auth()->id())
                        ->where('paket_member_id', $paketMember->id)
                        ->whereIn('status', Booking::STATUS_ACTIVE)
                        ->sum('durasi_jam');
                    $sisa = $paketMember->kuota_jam - $usedJam;
                    if ($sisa >= $durasi) {
                        $totalHarga = 0;
                        $isGratisMember = true;
                    }
                }
            }
        }

        // Logic Kupon Percent - hanya per_jam, tidak stack dengan gratis member
        $coupon = null;
        $discountAmount = 0;
        $totalHargaBeforeDiscount = $totalHarga;
        $couponCode = $request->coupon_code ? strtoupper(trim($request->coupon_code)) : null;
        if ($couponCode) {
            if ($request->tipe_sewa !== 'per_jam') {
                return back()->withErrors(['coupon_code' => 'Kupon hanya berlaku untuk sewa per jam'])->withInput();
            }
            if ($isGratisMember) {
                return back()->withErrors(['coupon_code' => 'Kupon tidak bisa dipakai bersama kuota gratis member'])->withInput();
            }
            $coupon = \App\Models\Coupon::where('code', $couponCode)->first();
            if (!$coupon) {
                return back()->withErrors(['coupon_code' => 'Kode kupon tidak ditemukan'])->withInput();
            }
            if (!$coupon->is_active) {
                return back()->withErrors(['coupon_code' => 'Kupon tidak aktif'])->withInput();
            }
            if ($coupon->isExpired()) {
                return back()->withErrors(['coupon_code' => 'Kupon sudah expired'])->withInput();
            }
            if ($coupon->quota !== null && $coupon->used_count >= $coupon->quota) {
                return back()->withErrors(['coupon_code' => 'Kuota kupon habis'])->withInput();
            }
            if (!$coupon->canBeUsedBy(auth()->id())) {
                return back()->withErrors(['coupon_code' => 'Kupon sudah dipakai maksimal '.$coupon->per_user_limit.'x'])->withInput();
            }
            if ($totalHarga < $coupon->min_amount) {
                return back()->withErrors(['coupon_code' => 'Minimal belanja Rp'.number_format($coupon->min_amount,0,',','.').' untuk kupon ini'])->withInput();
            }
            // Hitung diskon percent
            $discountAmount = $coupon->calculateDiscount($totalHarga);
            $totalHarga = max(0, $totalHarga - $discountAmount);
        }

        try {
            DB::beginTransaction();

            $durasiJam = 1;
            if ($request->tipe_sewa === 'per_jam') {
                $s = Carbon::parse($jamMulai);
                $e = Carbon::parse($jamSelesai);
                if ($e->lessThanOrEqualTo($s)) $e->addDay();
                $durasiJam = (int) ceil($s->diffInMinutes($e) / 60);
            } else {
                $durasiJam = 16; // full day 08-00
            }

            $booking = Booking::create([
                'user_id' => auth()->id(),
                'lapangan_id' => $lapangan->id,
                'paket_member_id' => $totalHarga === 0 && $isGratisMember ? auth()->user()->member_package_id : null,
                'coupon_id' => $coupon ? $coupon->id : null,
                'jenis_kegiatan' => $request->jenis_kegiatan,
                'tipe_sewa' => $request->tipe_sewa,
                'tanggal' => $request->tanggal,
                'tanggal_selesai' => $request->tipe_sewa === 'harian' ? $request->tanggal : null,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'durasi_jam' => $durasiJam,
                'total_harga' => $totalHarga,
                'discount_amount' => $discountAmount,
                'total_harga_before_discount' => $discountAmount > 0 ? $totalHargaBeforeDiscount : null,
                'status' => $request->metode === 'transfer' ? 'pending_verification' : ($request->metode === 'cash' ? 'pending' : 'pending'),
                'catatan' => $request->catatan,
            ]);

            // Catat penggunaan kupon
            if ($coupon && $discountAmount > 0) {
                // Lock untuk cegah race
                $couponFresh = \App\Models\Coupon::where('id', $coupon->id)->lockForUpdate()->first();
                if ($couponFresh->quota !== null && $couponFresh->used_count >= $couponFresh->quota) {
                    throw new \Exception('Kuota kupon habis (race)');
                }
                \App\Models\CouponUsage::create([
                    'coupon_id' => $coupon->id,
                    'user_id' => auth()->id(),
                    'booking_id' => $booking->id,
                    'discount_amount' => $discountAmount,
                ]);
                $couponFresh->increment('used_count');
            }

            $paymentStatus = 'pending';
            if ($totalHarga === 0) $paymentStatus = 'paid';

            Payment::create([
                'booking_id' => $booking->id,
                'metode' => $request->metode,
                'amount' => $totalHarga,
                'status' => $paymentStatus,
                'paid_at' => $totalHarga === 0 ? now() : null,
            ]);

            DB::commit();

            if ($request->metode === 'midtrans' && $totalHarga > 0) {
                return redirect()->route('booking.show', $booking->id)->with('success', 'Booking berhasil! Silakan lanjutkan pembayaran Midtrans (simulasi).');
            }

            return redirect()->route('booking.show', $booking->id)->with('success', 'Booking berhasil dibuat! Status: '. $booking->status);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal booking: '.$e->getMessage()])->withInput();
        }
    }

    public function show(Booking $booking)
    {
        if ($booking->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }
        $booking->load(['payment','lapangan','user','coupon']);
        return view('booking.show', compact('booking'));
    }

    public function uploadBukti(Request $request, Booking $booking)
    {
        $request->validate(['bukti' => 'required|image|max:2048']);
        if ($booking->user_id !== auth()->id()) abort(403);
        $path = $request->file('bukti')->store('bukti_transfer','public');
        $booking->payment()->update(['bukti_transfer_path' => $path, 'status' => 'pending']);
        $booking->update(['status' => 'pending_verification']);
        return back()->with('success','Bukti transfer berhasil diupload, menunggu verifikasi admin');
    }

    public function cancel(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) abort(403);
        if (!in_array($booking->status, ['pending','pending_verification','paid'])) {
            return back()->withErrors(['error' => 'Booking tidak bisa dibatalkan pada status ini']);
        }
        DB::transaction(function() use ($booking) {
            // Kembalikan kuota kupon jika masih pending
            if ($booking->coupon_id && in_array($booking->status, ['pending','pending_verification'])) {
                $usage = \App\Models\CouponUsage::where('booking_id', $booking->id)->first();
                if ($usage) {
                    \App\Models\Coupon::where('id', $booking->coupon_id)->decrement('used_count');
                    $usage->delete();
                }
            }
            $booking->update(['status' => 'cancelled']);
            $booking->payment()->update(['status' => 'failed']);
        });
        return back()->with('success','Booking dibatalkan (jeda 30 menit tidak berlaku, kupon dikembalikan jika pending)');
    }
}
