<?php

namespace Tests\Unit;

use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsVenue;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase, SeedsVenue;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedVenue();
    }

    public function test_calculate_discount_percent(): void
    {
        $coupon = $this->makeCoupon(['value' => 10, 'max_discount' => null]);

        $this->assertSame(10000, $coupon->calculateDiscount(100000));
    }

    public function test_calculate_discount_dibatasi_max_discount(): void
    {
        $coupon = $this->makeCoupon(['value' => 20, 'max_discount' => 15000]);

        $this->assertSame(15000, $coupon->calculateDiscount(100000));
    }

    public function test_calculate_discount_tidak_melebihi_total(): void
    {
        $coupon = $this->makeCoupon(['value' => 100, 'max_discount' => null]);

        $this->assertSame(5000, $coupon->calculateDiscount(5000));
    }

    public function test_is_expired(): void
    {
        $this->assertFalse($this->makeCoupon(['code' => 'EXP1', 'expired_at' => now()->addDay()])->isExpired());
        $this->assertTrue($this->makeCoupon(['code' => 'EXP2', 'expired_at' => now()->subDay()])->isExpired());
        $this->assertFalse($this->makeCoupon(['code' => 'EXP3', 'expired_at' => null])->isExpired());
    }

    public function test_is_available(): void
    {
        $this->assertTrue($this->makeCoupon(['code' => 'AVA1'])->isAvailable());
        $this->assertFalse($this->makeCoupon(['code' => 'AVA2', 'is_active' => false])->isAvailable());
        $this->assertFalse($this->makeCoupon(['code' => 'AVA3', 'quota' => 5, 'used_count' => 5])->isAvailable());
        $this->assertFalse($this->makeCoupon(['code' => 'AVA4', 'expired_at' => now()->subDay()])->isAvailable());
    }

    public function test_can_be_used_by_menghormati_per_user_limit(): void
    {
        $coupon = $this->makeCoupon(['per_user_limit' => 1]);
        $booking = $this->createBooking();

        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => $booking->user_id,
            'booking_id' => $booking->id,
            'discount_amount' => 5000,
        ]);

        $this->assertFalse($coupon->canBeUsedBy($booking->user_id));
        $this->assertTrue($coupon->canBeUsedBy($this->makeUser()->id));
    }

    public function test_per_user_limit_besar_masih_bisa_dipakai(): void
    {
        $coupon = $this->makeCoupon(['per_user_limit' => 10]);

        $this->assertTrue($coupon->canBeUsedBy($this->makeUser()->id));
    }
}
