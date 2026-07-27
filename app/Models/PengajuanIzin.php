<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanIzin extends Model
{
    use HasFactory;

    // Mengizinkan semua kolom diisi
    protected $guarded = [];

    // Relasi: Pengajuan Izin dimiliki oleh 1 User (Pegawai)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}