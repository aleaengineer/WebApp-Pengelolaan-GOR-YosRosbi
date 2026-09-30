<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    protected $fillable = [
        'user_id','lapangan_id','kode_booking','paket_member_id','coupon_id','jenis_kegiatan','tipe_sewa',
        'tanggal','tanggal_selesai','jam_mulai','jam_selesai','durasi_jam','total_harga','discount_amount','total_harga_before_discount','status','catatan','is_override_buffer','override_reason'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_selesai' => 'date',
        'is_override_buffer' => 'boolean',
    ];

    const STATUS_ACTIVE = ['pending','pending_verification','paid','confirmed','completed'];

    /**
     * Transisi status yang diizinkan (dipakai admin saat update status).
     * Status completed/cancelled/expired bersifat terminal.
     */
    const STATUS_TRANSITIONS = [
        'pending' => ['paid', 'confirmed', 'completed', 'cancelled'],
        'pending_verification' => ['paid', 'confirmed', 'completed', 'cancelled'],
        'paid' => ['confirmed', 'completed', 'cancelled'],
        'confirmed' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
        'expired' => [],
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function couponUsage()
    {
        return $this->hasOne(CouponUsage::class);
    }

    public function getBlockedUntilAttribute()
    {
        $buffer = (int) \App\Models\Setting::get('buffer_menit', 30);
        if ($this->tipe_sewa === 'harian') {
            $buffer = (int) \App\Models\Setting::get('buffer_harian_menit', 30);
        }
        $end = Carbon::parse($this->tanggal->format('Y-m-d').' '.$this->jam_selesai);
        if ($this->tipe_sewa === 'harian' && $this->tanggal_selesai) {
            $end = Carbon::parse($this->tanggal_selesai->format('Y-m-d').' '.$this->jam_selesai);
        }
        return $end->copy()->addMinutes($buffer);
    }

    /**
     * Kembalikan kuota kupon yang terpakai pada booking ini (dipanggil saat cancel/expire).
     */
    public function restoreCouponUsage(): void
    {
        if (!$this->coupon_id) return;
        $usage = CouponUsage::where('booking_id', $this->id)->first();
        if ($usage) {
            Coupon::where('id', $this->coupon_id)->decrement('used_count');
            $usage->delete();
        }
    }

    public function scopeActive($q)
    {
        return $q->whereIn('status', self::STATUS_ACTIVE);
    }
}
