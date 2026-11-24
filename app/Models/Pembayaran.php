<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Pembayaran Model
 * 
 * Represents payment transactions
 */
class Pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'tagihan_id',
        'item_uang_pangkal_id',
        'tanggal_bayar',
        'jumlah_bayar',
        'created_by',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'jumlah_bayar' => 'decimal:2',
    ];

    /**
     * Relationship: Belongs to Tagihan
     */
    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }

    /**
     * Relationship: Belongs to Item Uang Pangkal
     */
    public function itemUangPangkal()
    {
        return $this->belongsTo(ItemUangPangkal::class, 'item_uang_pangkal_id');
    }

    /**
     * Relationship: Belongs to User (who created the payment)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeByPeriode($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_bayar', [$startDate, $endDate]);
    }

    /**
     * Scope: Filter by month and year
     */
    public function scopeByBulan($query, $bulan, $tahun)
    {
        return $query->whereMonth('tanggal_bayar', $bulan)
                     ->whereYear('tanggal_bayar', $tahun);
    }

    /**
     * Scope: Today's payments
     */
    public function scopeToday($query)
    {
        return $query->whereDate('tanggal_bayar', Carbon::today());
    }

    /**
     * Scope: This month's payments
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('tanggal_bayar', Carbon::now()->month)
                     ->whereYear('tanggal_bayar', Carbon::now()->year);
    }
}
