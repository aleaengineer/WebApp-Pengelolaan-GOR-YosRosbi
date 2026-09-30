<?php

namespace Tests\Feature;

use App\Models\CouponUsage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\Concerns\SeedsVenue;
use Tests\TestCase;

class ExpireStaleBookingsTest extends TestCase
{
    use RefreshDatabase, SeedsVenue;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedVenue();
    }

    public function test_booking_pending_kemarin_jadi_expired_dan_kupon_kembali(): void
    {
        $user = $this->makeUser();
        $coupon = $this->makeCoupon();
        $booking = $this->createBooking([
            'user_id' => $user->id,
            'coupon_id' => $coupon->id,
            'status' => 'pending',
            'tanggal' => today()->subDay()->toDateString(),
        ]);
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => $user->id,
            'booking_id' => $booking->id,
            'discount_amount' => 5000,
        ]);
        $coupon->increment('used_count');
        $this->makePayment($booking, ['status' => 'pending']);

        Artisan::call('booking:expire-stale');

        $booking->refresh();
        $this->assertSame('expired', $booking->status);
        $this->assertSame('failed', $booking->payment->fresh()->status);
        $this->assertSame(0, $coupon->fresh()->used_count);
        $this->assertDatabaseMissing('coupon_usages', ['booking_id' => $booking->id]);
    }

    public function test_booking_pending_hari_ini_tidak_diexpire(): void
    {
        $booking = $this->createBooking(['status' => 'pending', 'tanggal' => today()->toDateString()]);
        $this->makePayment($booking);

        Artisan::call('booking:expire-stale');

        $this->assertSame('pending', $booking->fresh()->status);
    }

    public function test_booking_yang_sudah_dibayar_tidak_diexpire(): void
    {
        $booking = $this->createBooking(['status' => 'pending', 'tanggal' => today()->subDay()->toDateString()]);
        $this->makePayment($booking, ['status' => 'paid', 'paid_at' => now()]);

        Artisan::call('booking:expire-stale');

        $this->assertSame('pending', $booking->fresh()->status);
        $this->assertSame('paid', $booking->payment->fresh()->status);
    }

    public function test_booking_future_tidak_diexpire(): void
    {
        $booking = $this->createBooking(['status' => 'pending', 'tanggal' => '2030-01-15']);
        $this->makePayment($booking);

        Artisan::call('booking:expire-stale');

        $this->assertSame('pending', $booking->fresh()->status);
    }

    public function test_command_melaporkan_jumlah_yang_diexpire(): void
    {
        $this->createBooking(['status' => 'pending', 'tanggal' => today()->subDay()->toDateString()]);
        $this->createBooking(['status' => 'pending_verification', 'tanggal' => today()->subDays(2)->toDateString()]);

        Artisan::call('booking:expire-stale');

        $this->assertStringContainsString('2 booking ditandai expired', Artisan::output());
    }
}
