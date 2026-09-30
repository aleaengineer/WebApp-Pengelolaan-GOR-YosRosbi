<?php

namespace Tests\Unit;

use App\Models\BlokirJadwal;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsVenue;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase, SeedsVenue;

    protected BookingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedVenue();
        $this->service = new BookingService();
    }

    public function test_slot_kosong_tersedia(): void
    {
        $result = $this->service->checkAvailability($this->lapangan->id, '2030-01-15', '08:00', '09:00');

        $this->assertTrue($result['available']);
        $this->assertNull($result['reason']);
    }

    public function test_bentrok_booking_aktif_dengan_saran_slot(): void
    {
        $this->createBooking(['jam_mulai' => '08:00', 'jam_selesai' => '09:00', 'status' => 'paid']);

        $result = $this->service->checkAvailability($this->lapangan->id, '2030-01-15', '09:00', '10:00');

        $this->assertFalse($result['available']);
        $this->assertStringContainsString('Bentrok jeda pembersihan 30 menit', $result['reason']);
        $this->assertSame('09:30', $result['suggestion']);
    }

    public function test_slot_tepat_setelah_buffer_tersedia(): void
    {
        $this->createBooking(['jam_mulai' => '08:00', 'jam_selesai' => '09:00', 'status' => 'paid']);

        $result = $this->service->checkAvailability($this->lapangan->id, '2030-01-15', '09:30', '10:30');

        $this->assertTrue($result['available']);
    }

    public function test_booking_cancelled_tidak_menghalangi_slot(): void
    {
        $this->createBooking(['jam_mulai' => '08:00', 'jam_selesai' => '09:00', 'status' => 'cancelled']);

        $result = $this->service->checkAvailability($this->lapangan->id, '2030-01-15', '08:00', '09:00');

        $this->assertTrue($result['available']);
    }

    public function test_exclude_booking_id_mengabaikan_booking_tertentu(): void
    {
        $booking = $this->createBooking(['jam_mulai' => '08:00', 'jam_selesai' => '09:00', 'status' => 'paid']);

        $result = $this->service->checkAvailability($this->lapangan->id, '2030-01-15', '08:00', '09:00', 'per_jam', $booking->id);

        $this->assertTrue($result['available']);
    }

    public function test_blokir_jadwal_menyebabkan_tidak_tersedia(): void
    {
        BlokirJadwal::create([
            'lapangan_id' => $this->lapangan->id,
            'tanggal' => '2030-01-15',
            'jam_mulai' => '10:00',
            'jam_selesai' => '11:00',
            'alasan' => 'Maintenance',
        ]);

        $result = $this->service->checkAvailability($this->lapangan->id, '2030-01-15', '10:00', '11:00');

        $this->assertFalse($result['available']);
        $this->assertSame('Jadwal diblokir admin', $result['reason']);
        $this->assertNull($result['suggestion']);
    }

    public function test_is_override_mengabaikan_semua_cek(): void
    {
        $this->createBooking(['jam_mulai' => '08:00', 'jam_selesai' => '09:00', 'status' => 'paid']);

        $result = $this->service->checkAvailability($this->lapangan->id, '2030-01-15', '08:00', '09:00', 'per_jam', null, true);

        $this->assertTrue($result['available']);
    }

    public function test_generate_fixed_slots_dari_jam_operasional(): void
    {
        $slots = $this->service->generateFixedSlots('2030-01-15');

        // 08:00 - 00:00 dengan siklus 90 menit (60 sewa + 30 jeda) = 11 slot
        $this->assertCount(11, $slots);
        $this->assertSame(['start' => '08:00', 'end' => '09:00'], $slots[0]);
        $this->assertSame(['start' => '09:30', 'end' => '10:30'], $slots[1]);
        $this->assertSame(['start' => '23:00', 'end' => '00:00'], $slots[10]);
    }

    public function test_durasi_harian_dihitung_dari_jam_operasional(): void
    {
        $this->assertSame(16, $this->service->durasiHarian());
    }

    public function test_durasi_harian_berubah_jika_jam_operasional_diubah(): void
    {
        \App\Models\Setting::set('jam_operasional_mulai', '07:00');
        \App\Models\Setting::set('jam_operasional_selesai', '21:00');

        $this->assertSame(14, $this->service->durasiHarian());
    }

    public function test_dalam_jam_operasional(): void
    {
        $this->assertNull($this->service->dalamJamOperasional('08:00', '09:00'));
        $this->assertNull($this->service->dalamJamOperasional('23:00', '00:00'));
        $this->assertNotNull($this->service->dalamJamOperasional('07:00', '08:00'));
        $this->assertNotNull($this->service->dalamJamOperasional('23:00', '01:00'));
        $this->assertNotNull($this->service->dalamJamOperasional('08:00', '08:00'));
    }

    public function test_find_next_available_slot_menjawab_slot_bebas_berikutnya(): void
    {
        $this->createBooking(['jam_mulai' => '08:00', 'jam_selesai' => '09:00', 'status' => 'paid']);

        $slot = $this->service->findNextAvailableSlot(
            $this->lapangan->id,
            '2030-01-15',
            \Carbon\Carbon::parse('2030-01-15 08:00')
        );

        $this->assertSame('09:30', $slot);
    }

    public function test_hitung_harga_per_jam_non_member(): void
    {
        $user = $this->makeUser();

        $harga = $this->service->hitungHarga($user, 'per_jam', '08:00', '10:00');

        $this->assertSame(100000, $harga['total']);
        $this->assertSame(2, $harga['durasi_jam']);
        $this->assertFalse($harga['is_gratis_member']);
    }

    public function test_hitung_harga_per_jam_dibulatkan_ke_atas(): void
    {
        $user = $this->makeUser();

        // 90 menit = 2 jam (ceil)
        $harga = $this->service->hitungHarga($user, 'per_jam', '08:00', '09:30');

        $this->assertSame(2, $harga['durasi_jam']);
        $this->assertSame(100000, $harga['total']);
    }

    public function test_hitung_harga_member_expired_tidak_dapat_harga_member(): void
    {
        $paket = $this->seedPaket();
        $user = $this->makeMemberUser($paket, ['member_expired_at' => now()->subDay()]);

        $harga = $this->service->hitungHarga($user, 'per_jam', '08:00', '10:00');

        $this->assertSame(100000, $harga['total']);
    }

    public function test_kuota_member_cukup_menghasilkan_gratis(): void
    {
        $paket = $this->seedPaket(10);
        $user = $this->makeMemberUser($paket);

        $harga = $this->service->hitungHarga($user, 'per_jam', '08:00', '10:00');

        $this->assertSame(0, $harga['total']);
        $this->assertTrue($harga['is_gratis_member']);
        $this->assertSame(2, $harga['durasi_jam']);
    }

    public function test_kuota_member_tidak_cukup_maka_bayar_harga_member(): void
    {
        $paket = $this->seedPaket(1);
        $user = $this->makeMemberUser($paket);

        // butuh 2 jam, sisa kuota 1 jam
        $harga = $this->service->hitungHarga($user, 'per_jam', '08:00', '10:00');

        $this->assertSame(80000, $harga['total']);
        $this->assertFalse($harga['is_gratis_member']);
    }

    public function test_kuota_member_hanya_menghitung_booking_aktif(): void
    {
        $paket = $this->seedPaket(2);
        $user = $this->makeMemberUser($paket);
        $this->createBooking([
            'user_id' => $user->id,
            'paket_member_id' => $paket->id,
            'status' => 'cancelled',
            'durasi_jam' => 2,
        ]);

        $harga = $this->service->hitungHarga($user, 'per_jam', '08:00', '10:00');

        $this->assertSame(0, $harga['total']);
        $this->assertTrue($harga['is_gratis_member']);
    }

    public function test_hitung_harga_harian(): void
    {
        $user = $this->makeUser();

        $harga = $this->service->hitungHarga($user, 'harian', '08:00', '00:00');

        $this->assertSame(1500000, $harga['total']);
        $this->assertSame(16, $harga['durasi_jam']);
        $this->assertFalse($harga['is_gratis_member']);
    }
}
