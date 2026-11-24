<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingSpp extends Model
{
    use HasFactory;

    protected $table = 'setting_spps';

    protected $fillable = [
        'jenjang_id',
        'kelas',
        'kategori',
        'nominal',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function jenjang()
    {
        return $this->belongsTo(Jenjang::class);
    }
}
