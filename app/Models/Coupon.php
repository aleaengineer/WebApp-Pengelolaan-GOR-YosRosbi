<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    protected $fillable = [
        'code','name','type','value','max_discount','min_amount','quota','used_count','per_user_limit','tipe_sewa','is_active','expired_at','created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expired_at' => 'datetime',
    ];

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isExpired(): bool
    {
        return $this->expired_at && Carbon::parse($this->expired_at)->isPast();
    }

    public function isAvailable(): bool
    {
        if (!$this->is_active) return false;
        if ($this->isExpired()) return false;
        if ($this->quota !== null && $this->used_count >= $this->quota) return false;
        return true;
    }

    public function canBeUsedBy($userId): bool
    {
        if ($this->per_user_limit === null) return true;
        $used = CouponUsage::where('coupon_id', $this->id)->where('user_id', $userId)->count();
        return $used < $this->per_user_limit;
    }

    public function calculateDiscount($totalHarga): int
    {
        if ($this->type === 'percent') {
            $discount = (int) floor($totalHarga * $this->value / 100);
            if ($this->max_discount !== null) {
                $discount = min($discount, (int) $this->max_discount);
            }
            return min($discount, $totalHarga);
        }
        return 0;
    }
}
