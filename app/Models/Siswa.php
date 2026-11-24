<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Siswa Model
 * 
 * Represents students in the school system
 */
class Siswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_siswa',
        'nisn',
        'tanggal_masuk',
        'status_siswa',
        'golongan_id',
        'golongan_kategori',
        'golongan_asal',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
    ];

    /**
     * Map shorthand golongan_kategori to full kategori name
     */
    public function getKategoriLabelAttribute(): string
    {
        $map = [
            'REG'  => 'Reguler',
            'ANP'  => 'Anak Pegawai',
            'APK'  => 'Anak Pegawai Khusus',
            'FULL' => 'Pindahan Satu Tahun',
            'HALF' => 'Pindahan Setengah Tahun',
        ];

        return $map[$this->golongan_kategori] ?? 'Reguler';
    }

    /**
     * Relationship: Belongs to Golongan
     */
    public function golongan()
    {
        return $this->belongsTo(Golongan::class);
    }

    /**
     * Relationship: Get Jenjang through Golongan
     */
    public function jenjang()
    {
        return $this->hasOneThrough(
            Jenjang::class,
            Golongan::class,
            'id', // Foreign key on golongans table
            'id', // Foreign key on jenjangs table
            'golongan_id', // Local key on siswas table
            'jenjang_id' // Local key on golongans table
        );
    }

    /**
     * Relationship: Has many Tagihans
     */
    public function tagihans()
    {
        return $this->hasMany(Tagihan::class);
    }

    /**
     * Relationship: Has many Pembayarans through Tagihans
     */
    public function pembayarans()
    {
        return $this->hasManyThrough(Pembayaran::class, Tagihan::class);
    }

    /**
     * Relationship: Has many Item Uang Pangkals
     */
    public function itemUangPangkals()
    {
        return $this->hasMany(ItemUangPangkal::class);
    }

    /**
     * Relationship: Has many Riwayat Transaksis
     */
    public function riwayatTransaksis()
    {
        return $this->hasMany(RiwayatTransaksi::class);
    }

    /**
     * Scope: Filter by Jenjang
     */
    public function scopeByJenjang($query, $jenjangId)
    {
        return $query->whereHas('golongan', function ($q) use ($jenjangId) {
            $q->where('jenjang_id', $jenjangId);
        });
    }

    /**
     * Scope: Filter by Golongan
     */
    public function scopeByGolongan($query, $golonganId)
    {
        return $query->where('golongan_id', $golonganId);
    }

    /**
     * Scope: Filter active students only
     */
    public function scopeAktif($query)
    {
        return $query->whereNotIn('status_siswa', ['non_aktif', 'pindah']);
    }

    /**
     * Get total outstanding SPP
     */
    public function getTotalTunggakanSppAttribute()
    {
        return $this->tagihans()
            ->where('jenis', 'spp')
            ->where('status', 'belum_lunas')
            ->sum('nominal');
    }

    /**
     * Get total outstanding Uang Pangkal
     */
    public function getTotalTunggakanUangPangkalAttribute()
    {
        return $this->itemUangPangkals()
            ->belumLunas()
            ->get()
            ->sum('sisa_nominal');
    }
}
