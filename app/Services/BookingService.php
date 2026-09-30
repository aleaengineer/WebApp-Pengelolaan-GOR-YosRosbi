<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BlokirJadwal;
use App\Models\Coupon;
use App\Models\HargaSewa;
use App\Models\PaketMember;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

class BookingService
{
    /**
     * Cek ketersediaan lapangan terhadap booking aktif (dengan buffer jeda) dan blokir jadwal.
     * $lock = true akan mengunci baris booking (panggil di dalam DB::transaction untuk mencegah double-booking).
     */
    public function checkAvailability($lapanganId, $tanggal, $jamMulai, $jamSelesai, $tipeSewa = 'per_jam', $excludeBookingId = null, $isOverride = false, bool $lock = false)
    {
        if ($isOverride) {
            return ['available' => true, 'reason' => null, 'suggestion' => null];
        }

        $buffer = $this->bufferMenit($tipeSewa);

        $newStart = Carbon::parse("$tanggal $jamMulai");
        $newEnd = Carbon::parse("$tanggal $jamSelesai");
        if ($newEnd->lessThanOrEqualTo($newStart)) $newEnd->addDay();
        $newBlockedEnd = $newEnd->copy()->addMinutes($buffer);

        // Ambil bookings aktif untuk tanggal tersebut (PHP loop agar kompatibel sqlite & mysql)
        $bookings = Booking::where('lapangan_id', $lapanganId)
            ->whereIn('status', Booking::STATUS_ACTIVE)
            ->whereDate('tanggal', $tanggal)
            ->when($excludeBookingId, fn($q) => $q->where('id','!=',$excludeBookingId))
            ->when($lock, fn($q) => $q->lockForUpdate())
            ->get();

        foreach ($bookings as $b) {
            $existStart = Carbon::parse($b->tanggal->format('Y-m-d').' '.$b->jam_mulai);
            $existEnd = Carbon::parse($b->tanggal->format('Y-m-d').' '.$b->jam_selesai);
            if ($existEnd->lessThanOrEqualTo($existStart)) $existEnd->addDay();
            $existBlockedEnd = $existEnd->copy()->addMinutes($buffer);
            // overlap jika newStart < existBlockedEnd && newBlockedEnd > existStart
            if ($newStart->lt($existBlockedEnd) && $newBlockedEnd->gt($existStart)) {
                $suggestion = $this->findNextAvailableSlot($lapanganId, $tanggal, $newStart, $buffer);
                return ['available' => false, 'reason' => 'Bentrok jeda pembersihan '.$buffer.' menit', 'suggestion' => $suggestion];
            }
        }

        // Cek blokir jadwal manual
        $blokirs = BlokirJadwal::where('lapangan_id', $lapanganId)
            ->whereDate('tanggal', $tanggal)
            ->get();
        foreach ($blokirs as $b) {
            $existStart = Carbon::parse($b->tanggal->format('Y-m-d').' '.$b->jam_mulai);
            $existEnd = Carbon::parse($b->tanggal->format('Y-m-d').' '.$b->jam_selesai);
            if ($existEnd->lessThanOrEqualTo($existStart)) $existEnd->addDay();
            if ($newStart->lt($existEnd) && $newBlockedEnd->gt($existStart)) {
                return ['available' => false, 'reason' => 'Jadwal diblokir admin', 'suggestion' => null];
            }
        }

        return ['available' => true, 'reason' => null, 'suggestion' => null];
    }

    public function bufferMenit(string $tipeSewa): int
    {
        if ($tipeSewa === 'harian') {
            return (int) Setting::get('buffer_harian_menit', 30);
        }
        return (int) Setting::get('buffer_menit', 30);
    }

    /**
     * Jam operasional dalam jam desimal. Selesai 00:00 dihitung 24:00.
     * @return array{0: float, 1: float} [mulai, selesai]
     */
    public function jamOperasional(): array
    {
        $mulai = (string) Setting::get('jam_operasional_mulai', '08:00');
        $selesai = (string) Setting::get('jam_operasional_selesai', '00:00');
        $mulaiJam = (int) substr($mulai, 0, 2) + ((int) substr($mulai, 3, 2)) / 60;
        $selesaiJam = (int) substr($selesai, 0, 2) + ((int) substr($selesai, 3, 2)) / 60;
        if ($selesaiJam <= $mulaiJam) $selesaiJam += 24;
        return [$mulaiJam, $selesaiJam];
    }

    /**
     * Durasi sewa harian (jam) dihitung dari jam operasional, bukan hardcode.
     */
    public function durasiHarian(): int
    {
        [$mulai, $selesai] = $this->jamOperasional();
        return (int) round($selesai - $mulai);
    }

    /**
     * Validasi jam booking terhadap jam operasional.
     * Return null jika valid, string pesan error jika tidak.
     */
    public function dalamJamOperasional(string $jamMulai, string $jamSelesai): ?string
    {
        [$mulaiOp, $selesaiOp] = $this->jamOperasional();
        $mulai = (int) substr($jamMulai, 0, 2) + ((int) substr($jamMulai, 3, 2)) / 60;
        $selesai = (int) substr($jamSelesai, 0, 2) + ((int) substr($jamSelesai, 3, 2)) / 60;
        if ($selesai <= $mulai) $selesai += 24;

        if ($mulai < $mulaiOp || $selesai > $selesaiOp) {
            return 'Booking hanya bisa dilakukan antara jam '.Setting::get('jam_operasional_mulai', '08:00').' - '.Setting::get('jam_operasional_selesai', '00:00');
        }
        return null;
    }

    /**
     * Hitung total harga & durasi booking.
     * Durasi per jam dibulatkan ke atas (ceil) dan konsisten dipakai untuk harga,
     * cek kuota member, dan kolom durasi_jam.
     * @return array{total: int, durasi_jam: int, is_gratis_member: bool}
     */
    public function hitungHarga(User $user, string $tipeSewa, string $jamMulai, string $jamSelesai): array
    {
        $hargaRow = HargaSewa::where('tipe', $tipeSewa)->first();
        $isMemberAktif = $user->member_package_id
            && $user->member_expired_at
            && Carbon::parse($user->member_expired_at)->isFuture();

        if ($tipeSewa === 'harian') {
            return [
                'total' => $hargaRow ? (int) $hargaRow->harga : 1500000,
                'durasi_jam' => $this->durasiHarian(),
                'is_gratis_member' => false,
            ];
        }

        $start = Carbon::parse($jamMulai);
        $end = Carbon::parse($jamSelesai);
        if ($end->lessThanOrEqualTo($start)) $end->addDay();
        $durasiJam = (int) ceil($start->diffInMinutes($end) / 60);

        $hargaPerJam = $hargaRow ? (int) $hargaRow->harga : 50000;
        if ($isMemberAktif && $hargaRow && $hargaRow->harga_member !== null) {
            $hargaPerJam = (int) $hargaRow->harga_member;
        }

        $total = $durasiJam * $hargaPerJam;
        $isGratis = false;

        // Kuota gratis hanya untuk member yang masih aktif (belum expired)
        if ($user->member_package_id && $isMemberAktif) {
            $paket = PaketMember::find($user->member_package_id);
            if ($paket) {
                $usedJam = Booking::where('user_id', $user->id)
                    ->where('paket_member_id', $paket->id)
                    ->whereIn('status', Booking::STATUS_ACTIVE)
                    ->sum('durasi_jam');
                if ($paket->kuota_jam - $usedJam >= $durasiJam) {
                    $total = 0;
                    $isGratis = true;
                }
            }
        }

        return ['total' => $total, 'durasi_jam' => $durasiJam, 'is_gratis_member' => $isGratis];
    }

    /**
     * Validasi & hitung diskon kupon.
     * @return array{error: ?string, coupon: ?Coupon, discount: int}
     */
    public function validasiKupon(?string $kode, ?User $user, string $tipeSewa, int $totalHarga, bool $isGratisMember): array
    {
        if (!$kode) {
            return ['error' => null, 'coupon' => null, 'discount' => 0];
        }
        if ($tipeSewa !== 'per_jam') {
            return ['error' => 'Kupon hanya berlaku untuk sewa per jam', 'coupon' => null, 'discount' => 0];
        }
        if ($isGratisMember) {
            return ['error' => 'Kupon tidak bisa dipakai bersama kuota gratis member', 'coupon' => null, 'discount' => 0];
        }
        $coupon = Coupon::where('code', strtoupper(trim($kode)))->first();
        if (!$coupon) {
            return ['error' => 'Kode kupon tidak ditemukan', 'coupon' => null, 'discount' => 0];
        }
        if (!$coupon->is_active) {
            return ['error' => 'Kupon tidak aktif', 'coupon' => null, 'discount' => 0];
        }
        if ($coupon->isExpired()) {
            return ['error' => 'Kupon sudah expired', 'coupon' => null, 'discount' => 0];
        }
        if ($coupon->quota !== null && $coupon->used_count >= $coupon->quota) {
            return ['error' => 'Kuota kupon habis', 'coupon' => null, 'discount' => 0];
        }
        if ($user && !$coupon->canBeUsedBy($user->id)) {
            return ['error' => 'Kupon sudah dipakai maksimal '.$coupon->per_user_limit.'x', 'coupon' => null, 'discount' => 0];
        }
        if ($totalHarga > 0 && $totalHarga < $coupon->min_amount) {
            return ['error' => 'Minimal belanja Rp'.number_format($coupon->min_amount,0,',','.').' untuk kupon ini', 'coupon' => null, 'discount' => 0];
        }

        $discount = $coupon->calculateDiscount($totalHarga);
        return ['error' => null, 'coupon' => $coupon, 'discount' => $discount];
    }

    public function findNextAvailableSlot($lapanganId, $tanggal, Carbon $from, $buffer = 30)
    {
        $jamMulaiOp = (string) Setting::get('jam_operasional_mulai', '08:00');
        $jamSelesaiOp = (string) Setting::get('jam_operasional_selesai', '00:00');

        $start = Carbon::parse("$tanggal $jamMulaiOp");
        $end = Carbon::parse("$tanggal $jamSelesaiOp");
        if ($jamSelesaiOp === '00:00' || $end->lessThanOrEqualTo($start)) {
            $end = Carbon::parse("$tanggal 00:00")->addDay();
        }

        // Cari slot mulai dari $from dibulatkan ke 30 menit
        $cursor = $from->copy()->ceilMinutes(30);
        if ($cursor->lessThan($start)) $cursor = $start->copy();

        // Ambil data sekali untuk cek loop (lebih efisien)
        $existingBookings = Booking::where('lapangan_id', $lapanganId)->whereIn('status', Booking::STATUS_ACTIVE)->whereDate('tanggal',$tanggal)->get();
        $blokirs = BlokirJadwal::where('lapangan_id',$lapanganId)->whereDate('tanggal',$tanggal)->get();

        for ($i=0; $i<32; $i++) {
            $slotStart = $cursor->copy();
            $slotEnd = $slotStart->copy()->addHour();
            if ($slotEnd->greaterThan($end)) break;
            $blockedEnd = $slotEnd->copy()->addMinutes($buffer);

            $conflict = false;
            foreach ($existingBookings as $b) {
                $es = Carbon::parse($b->tanggal->format('Y-m-d').' '.$b->jam_mulai);
                $ee = Carbon::parse($b->tanggal->format('Y-m-d').' '.$b->jam_selesai);
                if ($ee->lessThanOrEqualTo($es)) $ee->addDay();
                $ebe = $ee->copy()->addMinutes($buffer);
                if ($slotStart->lt($ebe) && $blockedEnd->gt($es)) { $conflict=true; break; }
            }
            if ($conflict) { $cursor->addMinutes(30); continue; }
            foreach ($blokirs as $bk) {
                $bs = Carbon::parse($bk->tanggal->format('Y-m-d').' '.$bk->jam_mulai);
                $be = Carbon::parse($bk->tanggal->format('Y-m-d').' '.$bk->jam_selesai);
                if ($be->lessThanOrEqualTo($bs)) $be->addDay();
                if ($slotStart->lt($be) && $blockedEnd->gt($bs)) { $conflict=true; break; }
            }
            if (!$conflict) return $slotStart->format('H:i');
            $cursor->addMinutes(30);
        }
        return null;
    }

    public function generateFixedSlots($tanggal)
    {
        $jamMulai = Setting::get('jam_operasional_mulai', '08:00');
        $jamSelesai = Setting::get('jam_operasional_selesai', '00:00');
        $start = Carbon::parse("$tanggal $jamMulai");
        $end = Carbon::parse("$tanggal $jamSelesai");
        if ($jamSelesai === '00:00' || $end->lessThanOrEqualTo($start)) {
            $end = Carbon::parse("$tanggal 00:00")->addDay();
        }

        $slots = [];
        $cursor = $start->copy();
        while ($cursor->copy()->addHour()->lessThanOrEqualTo($end)) {
            $slotStart = $cursor->format('H:i');
            $slotEnd = $cursor->copy()->addHour()->format('H:i');
            $slots[] = ['start' => $slotStart, 'end' => $slotEnd];
            $cursor->addMinutes(90); // 60 menit sewa + 30 jeda
        }
        return $slots;
    }
}
