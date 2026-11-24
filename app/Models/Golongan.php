<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Golongan Model
 * 
 * Represents class/grade groupings (KB, TK A, Kelas 1, etc.)
 */
class Golongan extends Model
{
    use HasFactory;

    protected $fillable = [
        'jenjang_id',
        'nama_golongan',
        'nominal_uang_pangkal',
        'nominal_uang_spp',
    ];

    protected $casts = [
        'nominal_uang_pangkal' => 'decimal:2',
        'nominal_uang_spp' => 'decimal:2',
    ];

    /**
     * Relationship: Belongs to Jenjang
     */
    public function jenjang()
    {
        return $this->belongsTo(Jenjang::class);
    }

    /**
     * Relationship: Has many Siswas
     */
    public function siswas()
    {
        return $this->hasMany(Siswa::class);
    }

    /**
     * Get active students count
     */
    public function getJumlahSiswaAktifAttribute()
    {
        return $this->siswas()->aktif()->count();
    }
}
