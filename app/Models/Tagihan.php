<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Tagihan Model
 * 
 * Represents billing records for students (SPP and Uang Pangkal)
 */
class Tagihan extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'tahun_ajaran_id',
        'jenis',
        'bulan',
        'tahun',
        'nominal',
        'status',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    /**
     * Relationship: Belongs to Siswa
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * Relationship: Belongs to Tahun Ajaran
     */
    public function tahunAjaran()
    {
        return $this->belongsTo(Tahun_ajaran::class, 'tahun_ajaran_id');
    }

    /**
     * Relationship: Has many Pembayarans
     */
    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }

    /**
     * Scope: Filter unpaid bills
     */
    public function scopeBelumLunas($query)
    {
        return $query->where('status', 'belum_lunas');
    }

    /**
     * Scope: Filter paid bills
     */
    public function scopeLunas($query)
    {
        return $query->where('status', 'lunas');
    }

    /**
     * Scope: Filter by student
     */
    public function scopeBySiswa($query, $siswaId)
    {
        return $query->where('siswa_id', $siswaId);
    }

    /**
     * Scope: Filter by type (spp/uang_pangkal)
     */
    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis', $jenis);
    }

    /**
     * Scope: Filter by month and year (for SPP)
     */
    public function scopeByPeriode($query, $bulan, $tahun)
    {
        return $query->where('bulan', $bulan)
                     ->where('tahun', $tahun);
    }

    /**
     * Get total paid amount
     */
    public function getTotalTerbayarAttribute()
    {
        return $this->pembayarans()->sum('jumlah_bayar');
    }

    /**
     * Get remaining amount
     */
    public function getSisaTagihanAttribute()
    {
        return $this->nominal - $this->total_terbayar;
    }
}
