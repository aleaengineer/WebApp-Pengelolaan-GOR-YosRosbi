<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CouponUsage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsVenue;
use Tests\TestCase;

class BookingStoreTest extends TestCase
{
    use RefreshDatabase, SeedsVenue;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedVenue();
    }

    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'tanggal' => '2030-01-15',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'jenis_kegiatan' => 'badminton',
            'tipe_sewa' => 'per_jam',
            'metode' => 'cash',
        ], $overrides);
    }

    public function test_store_per_jam_membuat_booking_dan_payment(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->post(route('booking.store'), $this->payload());

        $response->assertRedirect();
        $booking = Booking::latest('id')->first();
        $this->assertEquals($user->id, $booking->user_id);
        $this->assertSame(100000, $booking->total_harga);
        $this->assertSame(2, $booking->durasi_jam);
        $this->assertSame('pending', $booking->status);
        $this->assertMatchesRegularExpression('/^GR-\d{8}-\d{4}$/', $booking->kode_booking);
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'metode' => 'cash',
            'amount' => 100000,
            'status' => 'pending',
        ]);
    }

    public function test_store_menghasilkan_kode_booking_dari_tanggal_dan_id(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post(route('booking.store'), $this->payload(['tanggal' => '2030-03-05']));

        $booking = Booking::latest('id')->first();
        $this->assertSame('GR-20300305-'.str_pad((string) $booking->id, 4, '0', STR_PAD_LEFT), $booking->kode_booking);
    }

    public function test_store_transfer_menghasilkan_status_pending_verification(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post(route('booking.store'), $this->payload(['metode' => 'transfer']));

        $this->assertDatabaseHas('bookings', ['status' => 'pending_verification']);
    }

    public function test_store_harian_memakai_durasi_dari_jam_operasional(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post(route('booking.store'), $this->payload([
            'tipe_sewa' => 'harian',
            'jam_mulai' => '08:00',
            'jam_selesai' => '00:00',
        ]));

        $booking = Booking::latest('id')->first();
        $this->assertSame(1500000, $booking->total_harga);
        $this->assertSame(16, $booking->durasi_jam);
    }

    public function test_store_member_dengan_kuota_cukup_gratis(): void
    {
        $paket = $this->seedPaket(10);
        $user = $this->makeMemberUser($paket);

        $this->actingAs($user)->post(route('booking.store'), $this->payload());

        $booking = Booking::latest('id')->first();
        $this->assertSame(0, $booking->total_harga);
        $this->assertEquals($paket->id, $booking->paket_member_id);
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'amount' => 0,
            'status' => 'paid',
        ]);
    }

    public function test_store_member_kuota_tidak_cukup_bayar_harga_member(): void
    {
        $paket = $this->seedPaket(1);
        $user = $this->makeMemberUser($paket);

        $this->actingAs($user)->post(route('booking.store'), $this->payload());

        $booking = Booking::latest('id')->first();
        $this->assertSame(80000, $booking->total_harga); // 2 x 40000
        $this->assertNull($booking->paket_member_id);
    }

    public function test_store_dengan_kupon_valid_memberikan_diskon(): void
    {
        $user = $this->makeUser();
        $coupon = $this->makeCoupon(['value' => 10, 'max_discount' => 15000]);

        $this->actingAs($user)->post(route('booking.store'), $this->payload(['coupon_code' => 'test10']));

        $booking = Booking::latest('id')->first();
        $this->assertSame(90000, $booking->total_harga);
        $this->assertSame(10000, $booking->discount_amount);
        $this->assertSame(100000, $booking->total_harga_before_discount);
        $this->assertEquals($coupon->id, $booking->coupon_id);
        $this->assertDatabaseHas('coupon_usages', [
            'coupon_id' => $coupon->id,
            'user_id' => $user->id,
            'booking_id' => $booking->id,
            'discount_amount' => 10000,
        ]);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_kupon_ditolak_untuk_sewa_harian(): void
    {
        $user = $this->makeUser();
        $this->makeCoupon();

        $response = $this->actingAs($user)->post(route('booking.store'), $this->payload([
            'tipe_sewa' => 'harian',
            'jam_mulai' => '08:00',
            'jam_selesai' => '00:00',
            'coupon_code' => 'TEST10',
        ]));

        $response->assertSessionHasErrors('coupon_code');
    }

    public function test_kupon_ditolak_bersama_kuota_gratis_member(): void
    {
        $paket = $this->seedPaket(10);
        $user = $this->makeMemberUser($paket);
        $this->makeCoupon();

        $response = $this->actingAs($user)->post(route('booking.store'), $this->payload(['coupon_code' => 'TEST10']));

        $response->assertSessionHasErrors('coupon_code');
    }

    public function test_kupon_expired_ditolak(): void
    {
        $user = $this->makeUser();
        $this->makeCoupon(['expired_at' => now()->subDay()]);

        $this->actingAs($user)->post(route('booking.store'), $this->payload(['coupon_code' => 'TEST10']))
            ->assertSessionHasErrors('coupon_code');
    }

    public function test_kupon_kuota_habis_ditolak(): void
    {
        $user = $this->makeUser();
        $this->makeCoupon(['quota' => 5, 'used_count' => 5]);

        $this->actingAs($user)->post(route('booking.store'), $this->payload(['coupon_code' => 'TEST10']))
            ->assertSessionHasErrors('coupon_code');
    }

    public function test_kupon_melebihi_per_user_limit_ditolak(): void
    {
        $user = $this->makeUser();
        $coupon = $this->makeCoupon(['per_user_limit' => 1]);
        $previous = $this->createBooking(['user_id' => $user->id, 'tanggal' => '2030-01-20']);
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => $user->id,
            'booking_id' => $previous->id,
            'discount_amount' => 5000,
        ]);

        $this->actingAs($user)->post(route('booking.store'), $this->payload(['coupon_code' => 'TEST10']))
            ->assertSessionHasErrors('coupon_code');
    }

    public function test_kupon_minimal_belanja_ditolak(): void
    {
        $user = $this->makeUser();
        $this->makeCoupon(['min_amount' => 150000]);

        // total hanya 100000 < 150000
        $this->actingAs($user)->post(route('booking.store'), $this->payload(['coupon_code' => 'TEST10']))
            ->assertSessionHasErrors('coupon_code');
    }

    public function test_kupon_tidak_ditemukan_ditolak(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post(route('booking.store'), $this->payload(['coupon_code' => 'TIDAKADA']))
            ->assertSessionHasErrors('coupon_code');
    }

    public function test_store_ditolak_jika_bentrok_dan_memberi_saran_slot(): void
    {
        $user = $this->makeUser();
        $this->createBooking(['jam_mulai' => '08:00', 'jam_selesai' => '09:00', 'status' => 'paid']);

        $response = $this->actingAs($user)->post(route('booking.store'), $this->payload([
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:00',
        ]));

        $response->assertSessionHasErrors('jam_mulai');
        $errors = session('errors')->getBag('default');
        $this->assertStringContainsString('Bentrok jeda pembersihan 30 menit', $errors->first('jam_mulai'));
        $this->assertStringContainsString('Saran slot tersedia', $errors->first('jam_mulai'));
    }

    public function test_store_ditolak_di_luar_jam_operasional(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->post(route('booking.store'), $this->payload([
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:00',
        ]));

        $response->assertSessionHasErrors('jam_mulai');
        $errors = session('errors')->getBag('default');
        $this->assertStringContainsString('Booking hanya bisa dilakukan antara jam', $errors->first('jam_mulai'));
    }

    public function test_store_ditolak_jika_format_jam_salah(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post(route('booking.store'), $this->payload(['jam_mulai' => '8 pagi']))
            ->assertSessionHasErrors('jam_mulai');
    }

    public function test_store_ditolak_untuk_jam_yang_sudah_lewat_hari_ini(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2030-01-15 09:00:00'));
        $user = $this->makeUser();

        $response = $this->actingAs($user)->post(route('booking.store'), $this->payload([
            'tanggal' => '2030-01-15',
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:00',
        ]));

        $response->assertSessionHasErrors('jam_mulai');
        $errors = session('errors')->getBag('default');
        $this->assertStringContainsString('jam yang sudah lewat', $errors->first('jam_mulai'));
    }

    public function test_store_ditolak_untuk_tanggal_masa_lalu(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post(route('booking.store'), $this->payload(['tanggal' => '2020-01-01']))
            ->assertSessionHasErrors('tanggal');
    }

    public function test_guest_tidak_bisa_booking(): void
    {
        $this->post(route('booking.store'), $this->payload())->assertRedirect(route('login'));
    }
}
