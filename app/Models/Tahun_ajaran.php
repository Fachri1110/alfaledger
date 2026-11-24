<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Tahun_ajaran Model
 * 
 * Represents academic years
 */
class Tahun_ajaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun_ajaran',
        'semester',
        'status',
    ];

    /**
     * Relationship: Has many Tagihans
     */
    public function tagihans()
    {
        return $this->hasMany(Tagihan::class, 'tahun_ajaran_id');
    }

    /**
     * Relationship: Has many Item Uang Pangkals
     */
    public function itemUangPangkals()
    {
        return $this->hasMany(ItemUangPangkal::class, 'tahun_ajaran_id');
    }

    /**
     * Scope: Filter active academic year
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Get active academic year
     */
    public static function getAktif()
    {
        return self::where('status', 'aktif')->first();
    }
}
