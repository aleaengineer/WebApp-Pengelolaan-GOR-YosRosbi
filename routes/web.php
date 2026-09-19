<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\CouponController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/jadwal', [HomeController::class, 'jadwal'])->name('jadwal');
Route::get('/api/check-availability', [HomeController::class, 'checkAvailability'])->name('api.check');
Route::get('/api/check-coupon', [HomeController::class, 'checkCoupon'])->name('api.coupon');
Route::get('/api/availability', function(\Illuminate\Http\Request $request, \App\Services\BookingService $service){
    $tanggal = $request->get('tanggal', date('Y-m-d'));
    $fixed = $service->generateFixedSlots($tanggal);
    $bookings = \App\Models\Booking::whereDate('tanggal', $tanggal)->whereIn('status', \App\Models\Booking::STATUS_ACTIVE)->get(['jam_mulai','jam_selesai','jenis_kegiatan','status']);
    $blokirs = \App\Models\BlokirJadwal::whereDate('tanggal', $tanggal)->get(['jam_mulai','jam_selesai','alasan']);
    return response()->json(['tanggal'=>$tanggal,'fixedSlots'=>$fixed,'bookings'=>$bookings,'blokirs'=>$blokirs]);
});

Route::get('/dashboard', function () {
    if (auth()->user()->isAdmin()) return redirect()->route('admin.bookings.index');
    $user = auth()->user();
    $paket = $user->paketMember;
    $sisaKuota = null; $statusMember = null;
    if ($paket && $user->member_expired_at) {
        $isExpired = \Carbon\Carbon::parse($user->member_expired_at)->isPast();
        $usedJam = \App\Models\Booking::where('user_id',$user->id)->where('paket_member_id',$paket->id)->whereIn('status', \App\Models\Booking::STATUS_ACTIVE)->sum('durasi_jam');
        $sisaKuota = $paket->kuota_jam - $usedJam;
        $statusMember = $isExpired ? 'expired' : 'active';
    }
    $totalBooking = \App\Models\Booking::where('user_id',$user->id)->count();
    $totalJam = \App\Models\Booking::where('user_id',$user->id)->whereIn('status',['paid','confirmed','completed'])->sum('durasi_jam');
    $totalPengeluaran = \App\Models\Booking::where('user_id',$user->id)->whereIn('status',['paid','confirmed','completed'])->sum('total_harga');
    $totalDiskon = \App\Models\Booking::where('user_id',$user->id)->sum('discount_amount');
    $upcoming = \App\Models\Booking::where('user_id',$user->id)->whereIn('status',['pending','pending_verification','paid','confirmed'])->whereDate('tanggal','>=',date('Y-m-d'))->orderBy('tanggal')->orderBy('jam_mulai')->first();
    $recent = \App\Models\Booking::where('user_id',$user->id)->latest()->take(3)->get();
    $promos = \App\Models\Coupon::where('is_active',true)->where(function($q){$q->whereNull('expired_at')->orWhere('expired_at','>',now());})->where('tipe_sewa','per_jam')->latest()->take(2)->get();
    return view('dashboard', compact('paket','sisaKuota','statusMember','totalBooking','totalJam','totalPengeluaran','totalDiskon','upcoming','recent','promos'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Customer Booking
    Route::get('/booking', [BookingController::class, 'index'])->name('booking.index');
    Route::get('/booking/create', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking/{booking}', [BookingController::class, 'show'])->name('booking.show');
    Route::post('/booking/{booking}/upload', [BookingController::class, 'uploadBukti'])->name('booking.upload');
    Route::post('/booking/{booking}/cancel', [BookingController::class, 'cancel'])->name('booking.cancel');

    // Admin
    Route::middleware('can:admin')->group(function(){
        Route::get('/admin/bookings', [AdminBookingController::class, 'index'])->name('admin.bookings.index');
        Route::get('/admin/bookings/{booking}', [AdminBookingController::class, 'show'])->name('admin.bookings.show');
        Route::post('/admin/bookings/{booking}/status', [AdminBookingController::class, 'updateStatus'])->name('admin.bookings.status');
        Route::get('/admin/blokir', [AdminBookingController::class, 'blokirIndex'])->name('admin.blokir.index');
        Route::post('/admin/blokir', [AdminBookingController::class, 'blokirStore'])->name('admin.blokir.store');
        Route::delete('/admin/blokir/{blokir}', [AdminBookingController::class, 'blokirDestroy'])->name('admin.blokir.destroy');
        Route::get('/admin/settings', [AdminBookingController::class, 'settings'])->name('admin.settings');
        Route::post('/admin/settings', [AdminBookingController::class, 'settingsUpdate'])->name('admin.settings.update');
        Route::get('/admin/laporan', [AdminBookingController::class, 'laporan'])->name('admin.laporan');
        Route::resource('/admin/coupons', CouponController::class)->names('admin.coupons');
        Route::post('/admin/coupons/{coupon}/toggle', [CouponController::class, 'toggle'])->name('admin.coupons.toggle');
    });
});

require __DIR__.'/auth.php';
