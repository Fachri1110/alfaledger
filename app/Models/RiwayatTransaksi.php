<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatTransaksi extends Model
{
    use HasFactory;

    protected $table = 'riwayat_transaksis';

    protected $fillable = [
        'siswa_id',
        'tanggal_transaksi',
        'function',
        'jenis',
        'nominal',
        'sisa',
        'nilai_akhir',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
        'nominal'           => 'decimal:2',
        'sisa'              => 'decimal:2',
        'nilai_akhir'       => 'decimal:2',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
