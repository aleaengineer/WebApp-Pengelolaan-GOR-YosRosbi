<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HargaSewa extends Model
{
    protected $fillable = ['lapangan_id','tipe','hari','jam_mulai','jam_selesai','harga','harga_member'];

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class);
    }
}
