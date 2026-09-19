<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaketMember extends Model
{
    protected $fillable = ['nama','slug','kuota_jam','durasi_hari','harga','deskripsi','is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
