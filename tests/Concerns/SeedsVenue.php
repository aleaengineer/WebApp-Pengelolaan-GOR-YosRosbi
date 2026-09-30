<?php

namespace Tests\Concerns;

use App\Models\Booking;
use App\Models\Coupon;
use App\Models\HargaSewa;
use App\Models\Lapangan;
use App\Models\PaketMember;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;

trait SeedsVenue
{
    protected Lapangan $lapangan;

    protected function seedVenue(): void
    {
        Setting::set('jam_operasional_mulai', '08:00');
        Setting::set('jam_operasional_selesai', '00:00');
        Setting::set('buffer_menit', '30');
        Setting::set('buffer_harian_menit', '30');

        $this->lapangan = Lapangan::create([
            'nama' => 'Lapangan Test',
            'jenis' => ['badminton', 'voly', 'basket'],
            'status' => 'active',
        ]);

        HargaSewa::create(['lapangan_id' => $this->lapangan->id, 'tipe' => 'per_jam', 'harga' => 50000, 'harga_member' => 40000]);
        HargaSewa::create(['lapangan_id' => $this->lapangan->id, 'tipe' => 'harian', 'harga' => 1500000, 'harga_member' => 1300000]);
    }

    protected function seedPaket(int $kuotaJam = 10): PaketMember
    {
        return PaketMember::create([
            'nama' => 'Paket Test',
            'slug' => 'paket-test-'.uniqid(),
            'kuota_jam' => $kuotaJam,
            'durasi_hari' => 30,
            'harga' => 350000,
            'is_active' => true,
        ]);
    }

    protected function makeUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    protected function makeAdmin(array $attributes = []): User
    {
        return User::factory()->create(array_merge(['role' => 'admin'], $attributes));
    }

    protected function makeMemberUser(PaketMember $paket, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'member_package_id' => $paket->id,
            'member_expired_at' => now()->addDays(10),
        ], $attributes));
    }

    protected function makeCoupon(array $attributes = []): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'TEST10',
            'name' => 'Kupon Test',
            'type' => 'percent',
            'value' => 10,
            'max_discount' => null,
            'min_amount' => 0,
            'quota' => null,
            'used_count' => 0,
            'per_user_limit' => 1,
            'tipe_sewa' => 'per_jam',
            'is_active' => true,
        ], $attributes));
    }

    protected function createBooking(array $attributes = []): Booking
    {
        return Booking::create(array_merge([
            'user_id' => $this->makeUser()->id,
            'lapangan_id' => $this->lapangan->id,
            'jenis_kegiatan' => 'badminton',
            'tipe_sewa' => 'per_jam',
            'tanggal' => '2030-01-15',
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:00',
            'durasi_jam' => 1,
            'total_harga' => 50000,
            'status' => 'paid',
        ], $attributes));
    }

    protected function makePayment(Booking $booking, array $attributes = []): Payment
    {
        return Payment::create(array_merge([
            'booking_id' => $booking->id,
            'metode' => 'transfer',
            'amount' => $booking->total_harga,
            'status' => 'pending',
        ], $attributes));
    }
}
