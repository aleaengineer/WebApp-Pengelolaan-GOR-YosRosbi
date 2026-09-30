<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CouponUsage;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SeedsVenue;
use Tests\TestCase;

class AdminBookingTest extends TestCase
{
    use RefreshDatabase, SeedsVenue;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedVenue();
    }

    public function test_guest_diarahkan_ke_login(): void
    {
        $this->get(route('admin.bookings.index'))->assertRedirect(route('login'));
    }

    public function test_customer_terlarang_akses_admin(): void
    {
        $this->actingAs($this->makeUser())->get(route('admin.bookings.index'))->assertForbidden();
        $this->actingAs($this->makeUser())->get(route('admin.laporan'))->assertForbidden();
        $this->actingAs($this->makeUser())->get(route('admin.settings'))->assertForbidden();
    }

    public function test_operator_bisa_akses_admin(): void
    {
        $operator = $this->makeUser(['role' => 'operator']);

        $this->actingAs($operator)->get(route('admin.bookings.index'))->assertOk();
    }

    public function test_update_status_ke_paid_menyinkronkan_payment(): void
    {
        $booking = $this->createBooking(['status' => 'pending_verification']);
        $this->makePayment($booking, ['status' => 'pending']);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.bookings.status', $booking->id), ['status' => 'paid'])
            ->assertRedirect();

        $this->assertSame('paid', $booking->fresh()->status);
        $payment = $booking->payment->fresh();
        $this->assertSame('paid', $payment->status);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_update_status_ke_completed_juga_melunasi_payment(): void
    {
        $booking = $this->createBooking(['status' => 'confirmed']);
        $this->makePayment($booking, ['status' => 'pending']);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.bookings.status', $booking->id), ['status' => 'completed'])
            ->assertRedirect();

        $this->assertSame('completed', $booking->fresh()->status);
        $this->assertSame('paid', $booking->payment->fresh()->status);
    }

    public function test_cancel_admin_mengembalikan_kupon(): void
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

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.bookings.status', $booking->id), ['status' => 'cancelled'])
            ->assertRedirect();

        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->assertSame(0, $coupon->fresh()->used_count);
        $this->assertDatabaseMissing('coupon_usages', ['booking_id' => $booking->id]);
        $this->assertSame('failed', $booking->payment->fresh()->status);
    }

    public function test_transisi_tidak_valid_ditolak(): void
    {
        $booking = $this->createBooking(['status' => 'completed']);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.bookings.status', $booking->id), ['status' => 'cancelled'])
            ->assertSessionHasErrors('error');

        $this->assertSame('completed', $booking->fresh()->status);
    }

    public function test_transisi_dari_expired_ditolak(): void
    {
        $booking = $this->createBooking(['status' => 'expired']);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.bookings.status', $booking->id), ['status' => 'paid'])
            ->assertSessionHasErrors('error');

        $this->assertSame('expired', $booking->fresh()->status);
    }

    public function test_filter_status_dengan_nilai_aneh_tidak_menghapus_semua(): void
    {
        $this->createBooking();

        // nilai filter tidak valid harus diabaikan, bukan menghasilkan error query
        $this->actingAs($this->makeAdmin())
            ->get(route('admin.bookings.index', ['status' => "'; DROP TABLE bookings;--"]))
            ->assertOk();
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_admin_upload_dan_hapus_qris(): void
    {
        Storage::fake('public');
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'qris_image' => UploadedFile::fake()->image('qris.png'),
        ])->assertRedirect();

        $path = Setting::get('qris');
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        // format tidak diizinkan ditolak
        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'qris_image' => UploadedFile::fake()->create('qris.svg', 10, 'image/svg+xml'),
        ])->assertSessionHasErrors('qris_image');

        // hapus QRIS
        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'hapus_qris' => '1',
        ])->assertRedirect();

        $this->assertNull(Setting::get('qris'));
        Storage::disk('public')->assertMissing($path);
    }

    public function test_laporan_menghitung_total_dan_data_grafik(): void
    {
        $this->createBooking(['status' => 'paid', 'total_harga' => 100000, 'durasi_jam' => 2, 'tanggal' => '2030-01-15']);
        $this->createBooking(['status' => 'confirmed', 'total_harga' => 50000, 'durasi_jam' => 1, 'tanggal' => '2030-01-16']);
        $this->createBooking(['status' => 'cancelled', 'total_harga' => 90000, 'durasi_jam' => 2, 'tanggal' => '2030-01-17']);

        $response = $this->actingAs($this->makeAdmin())->get(route('admin.laporan'));

        $response->assertOk();
        $response->assertViewHas('total', 150000);
        $response->assertViewHas('totalJam', 3);
        $perTanggal = $response->viewData('perTanggal');
        $this->assertCount(2, $perTanggal); // cancelled tidak masuk laporan
        $this->assertSame('2030-01-15', $perTanggal[0]['tanggal']);
        $this->assertSame(100000, $perTanggal[0]['pendapatan']);
        $this->assertSame(2, $perTanggal[0]['jam']);
    }
}
