<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['booking_id','metode','midtrans_order_id','midtrans_status','bukti_transfer_path','amount','status','paid_at'];
    protected $casts = ['paid_at' => 'datetime'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
