<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlokirJadwal extends Model
{
    protected $fillable = ['lapangan_id','tanggal','jam_mulai','jam_selesai','alasan','created_by'];
    protected $casts = ['tanggal' => 'date'];
}
