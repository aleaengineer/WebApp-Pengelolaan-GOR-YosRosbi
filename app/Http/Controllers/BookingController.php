<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Lapangan;
use App\Models\Payment;
use App\Models\PaketMember;
use App\Models\Setting;
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
        $rekening = Setting::get('rekening', 'BCA 1234567890 a.n. GOR Yos Rosbi');
        $qris = Setting::get('qris');
        $kontakWa = Setting::get('kontak_wa', '');
        return view('booking.create', compact('fixedSlots','tanggal','hargaPerJam','hargaHarian','rekening','qris','kontakWa'));
    }

    public function store(Request $request, BookingService $service)
    {
        $request->validate([
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i',
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
        $errorOperasional = $service->dalamJamOperasional($request->jam_mulai, $request->jam_selesai);
        if ($errorOperasional) {
            return back()->withErrors(['jam_mulai' => $errorOperasional])->withInput();
        }

        // Tidak boleh booking pada jam yang sudah lewat untuk hari ini
        if (Carbon::parse($request->tanggal)->isToday() && Carbon::parse($request->jam_mulai)->lte(now())) {
            return back()->withErrors(['jam_mulai' => 'Tidak bisa booking pada jam yang sudah lewat'])->withInput();
        }

        // Pre-check availability (di luar transaction untuk pesan error + saran slot)
        $check = $service->checkAvailability($lapangan->id, $request->tanggal, $request->jam_mulai, $request->jam_selesai, $request->tipe_sewa);
        if (!$check['available']) {
            $msg = $check['reason'];
            if ($check['suggestion']) $msg .= ". Saran slot tersedia: {$check['suggestion']}";
            return back()->withErrors(['jam_mulai' => $msg])->withInput();
        }

        $user = $request->user();

        // Hitung harga & validasi kupon (pre-check untuk pesan error per field)
        $harga = $service->hitungHarga($user, $request->tipe_sewa, $request->jam_mulai, $request->jam_selesai);
        $kupon = $service->validasiKupon($request->coupon_code, $user, $request->tipe_sewa, $harga['total'], $harga['is_gratis_member']);
        if ($kupon['error']) {
            return back()->withErrors(['coupon_code' => $kupon['error']])->withInput();
        }

        try {
            $booking = DB::transaction(function () use ($request, $service, $user, $lapangan, $harga, $kupon) {
                // Kunci paket member agar kuota tidak bisa terpakai bersamaan oleh booking paralel
                if ($user->member_package_id) {
                    PaketMember::whereKey($user->member_package_id)->lockForUpdate()->first();
                }

                // Re-check dengan lock (cegah double-booking pada request paralel)
                $check = $service->checkAvailability($lapangan->id, $request->tanggal, $request->jam_mulai, $request->jam_selesai, $request->tipe_sewa, null, false, true);
                if (!$check['available']) {
                    throw new \Exception($check['reason']);
                }

                // Hitung ulang di dalam transaction (kuota member & harga terbaru)
                $harga = $service->hitungHarga($user, $request->tipe_sewa, $request->jam_mulai, $request->jam_selesai);
                $kupon = $service->validasiKupon($request->coupon_code, $user, $request->tipe_sewa, $harga['total'], $harga['is_gratis_member']);
                if ($kupon['error']) {
                    throw new \Exception($kupon['error']);
                }

                $coupon = $kupon['coupon'];
                $discountAmount = $kupon['discount'];
                $totalHarga = max(0, $harga['total'] - $discountAmount);

                $booking = Booking::create([
                    'user_id' => $user->id,
                    'lapangan_id' => $lapangan->id,
                    'paket_member_id' => $totalHarga === 0 && $harga['is_gratis_member'] ? $user->member_package_id : null,
                    'coupon_id' => $coupon ? $coupon->id : null,
                    'jenis_kegiatan' => $request->jenis_kegiatan,
                    'tipe_sewa' => $request->tipe_sewa,
                    'tanggal' => $request->tanggal,
                    'tanggal_selesai' => $request->tipe_sewa === 'harian' ? $request->tanggal : null,
                    'jam_mulai' => $request->jam_mulai,
                    'jam_selesai' => $request->jam_selesai,
                    'durasi_jam' => $harga['durasi_jam'],
                    'total_harga' => $totalHarga,
                    'discount_amount' => $discountAmount,
                    'total_harga_before_discount' => $discountAmount > 0 ? $harga['total'] : null,
                    'status' => $request->metode === 'transfer' ? 'pending_verification' : 'pending',
                    'catatan' => $request->catatan,
                ]);

                // Catat penggunaan kupon dengan lock anti-race
                if ($coupon && $discountAmount > 0) {
                    $couponFresh = Coupon::where('id', $coupon->id)->lockForUpdate()->first();
                    if ($couponFresh->quota !== null && $couponFresh->used_count >= $couponFresh->quota) {
                        throw new \Exception('Kuota kupon habis');
                    }
                    $usedByUser = CouponUsage::where('coupon_id', $couponFresh->id)->where('user_id', $user->id)->count();
                    if ($couponFresh->per_user_limit !== null && $usedByUser >= $couponFresh->per_user_limit) {
                        throw new \Exception('Kupon sudah dipakai maksimal '.$couponFresh->per_user_limit.'x');
                    }
                    CouponUsage::create([
                        'coupon_id' => $coupon->id,
                        'user_id' => $user->id,
                        'booking_id' => $booking->id,
                        'discount_amount' => $discountAmount,
                    ]);
                    $couponFresh->increment('used_count');
                }

                Payment::create([
                    'booking_id' => $booking->id,
                    'metode' => $request->metode,
                    'amount' => $totalHarga,
                    'status' => $totalHarga === 0 ? 'paid' : 'pending',
                    'paid_at' => $totalHarga === 0 ? now() : null,
                ]);

                // Nomor order unik berbasis id (dalam transaction, bebas race)
                $booking->update([
                    'kode_booking' => 'GR-'.$booking->tanggal->format('Ymd').'-'.str_pad((string) $booking->id, 4, '0', STR_PAD_LEFT),
                ]);

                return $booking;
            });
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal booking: '.$e->getMessage()])->withInput();
        }

        if ($request->metode === 'midtrans' && $booking->total_harga > 0) {
            return redirect()->route('booking.show', $booking->id)->with('success', 'Booking berhasil! Silakan lanjutkan pembayaran Midtrans (simulasi).');
        }

        return redirect()->route('booking.show', $booking->id)->with('success', 'Booking berhasil dibuat! Status: '. $booking->status);
    }

    public function show(Booking $booking)
    {
        if ($booking->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }
        $booking->load(['payment','lapangan','user','coupon']);
        $rekening = Setting::get('rekening', 'BCA 1234567890 a.n. GOR Yos Rosbi');
        $qris = Setting::get('qris');
        $kontakWa = Setting::get('kontak_wa', '');
        return view('booking.show', compact('booking','rekening','qris','kontakWa'));
    }

    public function uploadBukti(Request $request, Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) abort(403);
        if (!in_array($booking->status, ['pending','pending_verification'])) {
            return back()->withErrors(['error' => 'Bukti transfer hanya bisa diupload pada booking yang menunggu pembayaran']);
        }
        if (!$booking->payment || $booking->payment->metode !== 'transfer') {
            return back()->withErrors(['error' => 'Booking ini tidak menggunakan metode transfer']);
        }
        $request->validate(['bukti' => 'required|mimes:jpg,jpeg,png,webp|max:2048']);
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
            $booking->restoreCouponUsage();
            $booking->update(['status' => 'cancelled']);
            $booking->payment()->update(['status' => 'failed']);
        });
        return back()->with('success','Booking dibatalkan, kupon dikembalikan jika sebelumnya digunakan');
    }
}
