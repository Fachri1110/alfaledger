<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ItemUangPangkal Model
 * 
 * Represents individual payment items for "Uang Pangkal" (entrance fee)
 * Supports partial payment with percentage tracking
 */
class ItemUangPangkal extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'tahun_ajaran_id',
        'nama_item',
        'nominal',
        'persentase_dibayar',
        'status',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'persentase_dibayar' => 'decimal:2',
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
        return $this->hasMany(\App\Models\Pembayaran::class, 'item_uang_pangkal_id');
    }

    /**
     * Scope: Filter by status
     */
    public function scopeBelumLunas($query)
    {
        return $query->where('status', 'belum_lunas');
    }

    /**
     * Scope: Filter by status lunas
     */
    public function scopeLunas($query)
    {
        return $query->where('status', 'lunas');
    }

    /**
     * Get remaining amount to be paid.
     * Dihitung dari nominal dikurangi jumlah bayar aktual (bukan dari persentase)
     * agar tidak ada floating-point drift saat cicilan.
     */
    public function getSisaNominalAttribute()
    {
        $nominal = (float) $this->nominal;
        if ($nominal <= 0) return 0;

        // Jika relasi pembayaran sudah di-load, gunakan sum aktual
        if ($this->relationLoaded('pembayarans')) {
            $terbayar = (float) $this->pembayarans->sum('jumlah_bayar');
        } else {
            // Fallback: hitung dari persentase (bisa ada sedikit error floating point)
            $terbayar = round($nominal * ((float) $this->persentase_dibayar / 100), 2);
        }

        return max(0, round($nominal - $terbayar, 2));
    }

    /**
     * Get paid amount.
     */
    public function getNominalTerbayarAttribute()
    {
        $nominal = (float) $this->nominal;
        if ($nominal <= 0) return 0;

        if ($this->relationLoaded('pembayarans')) {
            return round((float) $this->pembayarans->sum('jumlah_bayar'), 2);
        }

        return round($nominal * ((float) $this->persentase_dibayar / 100), 2);
    }

    /**
     * Check if item is fully paid
     */
    public function isLunas()
    {
        return $this->persentase_dibayar >= 100;
    }
}
