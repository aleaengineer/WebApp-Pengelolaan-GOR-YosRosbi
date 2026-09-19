<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    protected $fillable = [
        'user_id','lapangan_id','paket_member_id','coupon_id','jenis_kegiatan','tipe_sewa',
        'tanggal','tanggal_selesai','jam_mulai','jam_selesai','durasi_jam','total_harga','discount_amount','total_harga_before_discount','status','catatan','is_override_buffer','override_reason'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_selesai' => 'date',
        'is_override_buffer' => 'boolean',
    ];

    const STATUS_ACTIVE = ['pending','pending_verification','paid','confirmed','completed'];

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
        $buffer = 30;
        if ($this->tipe_sewa === 'harian') {
            // harian tetap 30 sesuai konfirmasi terakhir
            $buffer = 30;
        }
        $end = Carbon::parse($this->tanggal->format('Y-m-d').' '.$this->jam_selesai);
        if ($this->tipe_sewa === 'harian' && $this->tanggal_selesai) {
            $end = Carbon::parse($this->tanggal_selesai->format('Y-m-d').' '.$this->jam_selesai);
        }
        return $end->copy()->addMinutes($buffer);
    }

    public function scopeActive($q)
    {
        return $q->whereIn('status', self::STATUS_ACTIVE);
    }
}
