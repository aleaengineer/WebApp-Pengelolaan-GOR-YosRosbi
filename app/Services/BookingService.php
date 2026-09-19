<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BlokirJadwal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function checkAvailability($lapanganId, $tanggal, $jamMulai, $jamSelesai, $tipeSewa = 'per_jam', $excludeBookingId = null, $isOverride = false)
    {
        if ($isOverride) {
            return ['available' => true, 'reason' => null, 'suggestion' => null];
        }

        $buffer = (int) \App\Models\Setting::get('buffer_menit', 30);
        if ($tipeSewa === 'harian') {
            $buffer = (int) \App\Models\Setting::get('buffer_harian_menit', 30);
        }

        $newStart = Carbon::parse("$tanggal $jamMulai");
        $newEnd = Carbon::parse("$tanggal $jamSelesai");
        if ($newEnd->lessThanOrEqualTo($newStart)) $newEnd->addDay();
        $newBlockedEnd = $newEnd->copy()->addMinutes($buffer);

        // Ambil bookings aktif untuk tanggal tersebut (PHP loop agar kompatibel sqlite & mysql)
        $bookings = Booking::where('lapangan_id', $lapanganId)
            ->whereIn('status', Booking::STATUS_ACTIVE)
            ->whereDate('tanggal', $tanggal)
            ->when($excludeBookingId, fn($q) => $q->where('id','!=',$excludeBookingId))
            ->get();

        foreach ($bookings as $b) {
            $existStart = Carbon::parse($b->tanggal->format('Y-m-d').' '.$b->jam_mulai);
            $existEnd = Carbon::parse($b->tanggal->format('Y-m-d').' '.$b->jam_selesai);
            if ($existEnd->lessThanOrEqualTo($existStart)) $existEnd->addDay();
            $existBlockedEnd = $existEnd->copy()->addMinutes($buffer);
            // overlap jika newStart < existBlockedEnd && newBlockedEnd > existStart
            if ($newStart->lt($existBlockedEnd) && $newBlockedEnd->gt($existStart)) {
                $suggestion = $this->findNextAvailableSlot($lapanganId, $tanggal, $newStart, $buffer);
                return ['available' => false, 'reason' => 'Bentrok jeda pembersihan 30 menit', 'suggestion' => $suggestion];
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

    public function findNextAvailableSlot($lapanganId, $tanggal, Carbon $from, $buffer = 30)
    {
        $jamOperasionalMulai = \App\Models\Setting::get('jam_operasional_mulai', '08:00');
        $jamOperasionalSelesai = \App\Models\Setting::get('jam_operasional_selesai', '00:00');

        $start = Carbon::parse("$tanggal $jamOperasionalMulai");
        $end = Carbon::parse("$tanggal $jamOperasionalSelesai");
        if ($jamOperasionalSelesai === '00:00' || $end->lessThanOrEqualTo($start)) {
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
        $buffer = 30;
        $jamMulai = \App\Models\Setting::get('jam_operasional_mulai', '08:00');
        $jamSelesai = \App\Models\Setting::get('jam_operasional_selesai', '00:00');
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
            if ($slotEnd === '00:00') $slotEnd = '00:00';
            $slots[] = ['start' => $slotStart, 'end' => $slotEnd];
            $cursor->addMinutes(90); // 60 menit sewa + 30 jeda
        }
        return $slots;
    }
}
