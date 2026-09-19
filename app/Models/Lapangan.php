<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lapangan extends Model
{
    protected $fillable = ['nama','deskripsi','jenis','foto','status'];
    protected $casts = ['jenis' => 'array'];

    public function hargaSewas()
    {
        return $this->hasMany(HargaSewa::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
