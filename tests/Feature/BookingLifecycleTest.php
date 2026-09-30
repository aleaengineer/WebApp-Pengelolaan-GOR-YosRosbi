<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CouponUsage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SeedsVenue;
use Tests\TestCase;

class BookingLifecycleTest extends TestCase
{
    use RefreshDatabase, SeedsVenue;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedVenue();
    }

    public function test_user_tidak_bisa_lihat_booking_user_lain(): void
    {
        $booking = $this->createBooking();

        $this->actingAs($this->makeUser())->get(route('booking.show', $booking->id))->assertForbidden();
    }

    public function test_admin_bisa_lihat_booking_siapapun(): void
    {
        $booking = $this->createBooking();

        $this->actingAs($this->makeAdmin())->get(route('booking.show', $booking->id))->assertOk();
    }

    public function test_cancel_mengembalikan_kuota_kupon(): void
    {
        $user = $this->makeUser();
        $coupon = $this->makeCoupon(['value' => 10]);
        $booking = $this->createBooking([
            'user_id' => $user->id,
            'coupon_id' => $coupon->id,
            'total_harga' => 90000,
            'discount_amount' => 10000,
            'total_harga_before_discount' => 100000,
            'status' => 'pending',
        ]);
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => $user->id,
            'booking_id' => $booking->id,
            'discount_amount' => 10000,
        ]);
        $coupon->increment('used_count');
        $this->makePayment($booking, ['metode' => 'transfer']);

        $this->actingAs($user)->post(route('booking.cancel', $booking->id))->assertRedirect();

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertSame(0, $coupon->fresh()->used_count);
        $this->assertDatabaseMissing('coupon_usages', ['booking_id' => $booking->id]);
        $this->assertSame('failed', $booking->payment->fresh()->status);
    }

    public function test_cancel_dari_status_paid_juga_mengembalikan_kupon(): void
    {
        $user = $this->makeUser();
        $coupon = $this->makeCoupon();
        $booking = $this->createBooking([
            'user_id' => $user->id,
            'coupon_id' => $coupon->id,
            'status' => 'paid',
        ]);
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => $user->id,
            'booking_id' => $booking->id,
            'discount_amount' => 5000,
        ]);
        $coupon->increment('used_count');
        $this->makePayment($booking, ['status' => 'paid']);

        $this->actingAs($user)->post(route('booking.cancel', $booking->id))->assertRedirect();

        $this->assertSame(0, $coupon->fresh()->used_count);
        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_cancel_booking_completed_ditolak(): void
    {
        $user = $this->makeUser();
        $booking = $this->createBooking(['user_id' => $user->id, 'status' => 'completed']);

        $this->actingAs($user)->post(route('booking.cancel', $booking->id))->assertSessionHasErrors('error');
        $this->assertSame('completed', $booking->fresh()->status);
    }

    public function test_cancel_booking_user_lain_ditolak(): void
    {
        $booking = $this->createBooking();

        $this->actingAs($this->makeUser())->post(route('booking.cancel', $booking->id))->assertForbidden();
    }

    public function test_upload_bukti_transfer_berhasil(): void
    {
        Storage::fake('public');
        $user = $this->makeUser();
        $booking = $this->createBooking(['user_id' => $user->id, 'status' => 'pending_verification']);
        $this->makePayment($booking, ['metode' => 'transfer', 'status' => 'pending']);

        $response = $this->actingAs($user)->post(route('booking.upload', $booking->id), [
            'bukti' => UploadedFile::fake()->image('bukti.png'),
        ]);

        $response->assertRedirect();
        $path = $booking->payment->fresh()->bukti_transfer_path;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
        $this->assertSame('pending_verification', $booking->fresh()->status);
    }

    public function test_upload_bukti_format_tidak_diizinkan_ditolak(): void
    {
        Storage::fake('public');
        $user = $this->makeUser();
        $booking = $this->createBooking(['user_id' => $user->id, 'status' => 'pending_verification']);
        $this->makePayment($booking, ['metode' => 'transfer']);

        $this->actingAs($user)->post(route('booking.upload', $booking->id), [
            'bukti' => UploadedFile::fake()->create('bukti.svg', 10, 'image/svg+xml'),
        ])->assertSessionHasErrors('bukti');
    }

    public function test_upload_bukti_pada_booking_completed_ditolak(): void
    {
        $user = $this->makeUser();
        $booking = $this->createBooking(['user_id' => $user->id, 'status' => 'completed']);
        $this->makePayment($booking, ['metode' => 'transfer', 'status' => 'paid']);

        $this->actingAs($user)->post(route('booking.upload', $booking->id), [
            'bukti' => UploadedFile::fake()->image('bukti.png'),
        ])->assertSessionHasErrors('error');
    }

    public function test_upload_bukti_booking_bukan_transfer_ditolak(): void
    {
        $user = $this->makeUser();
        $booking = $this->createBooking(['user_id' => $user->id, 'status' => 'pending']);
        $this->makePayment($booking, ['metode' => 'cash']);

        $this->actingAs($user)->post(route('booking.upload', $booking->id), [
            'bukti' => UploadedFile::fake()->image('bukti.png'),
        ])->assertSessionHasErrors('error');
    }

    public function test_upload_bukti_user_lain_ditolak(): void
    {
        $booking = $this->createBooking();
        $this->makePayment($booking);

        $this->actingAs($this->makeUser())->post(route('booking.upload', $booking->id), [
            'bukti' => UploadedFile::fake()->image('bukti.png'),
        ])->assertForbidden();
    }
}
