<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Jenjang Model
 * 
 * Represents education levels (KB/TK, SD, SMP)
 */
class Jenjang extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_jenjang',
    ];

    /**
     * Relationship: Has many Golongans
     */
    public function golongans()
    {
        return $this->hasMany(Golongan::class);
    }

    /**
     * Relationship: Has many Siswas through Golongans
     */
    public function siswas()
    {
        return $this->hasManyThrough(Siswa::class, Golongan::class);
    }

    /**
     * Get active students count
     */
    public function getJumlahSiswaAktifAttribute()
    {
        return $this->siswas()->aktif()->count();
    }

    /**
     * Get total students (active + inactive)
     */
    public function getTotalSiswaAttribute()
    {
        return $this->siswas()->count();
    }
}
