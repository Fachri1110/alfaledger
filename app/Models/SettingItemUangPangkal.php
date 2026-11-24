<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingItemUangPangkal extends Model
{
    use HasFactory;

    protected $table = 'setting_item_uang_pangkals';

    protected $fillable = [
        'jenjang_id',
        'kelas',
        'kategori',
        'item_index',
        'nama_item',
        'nominal',
        'is_aktif',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'is_aktif' => 'boolean',
        'item_index' => 'integer',
    ];

    public function jenjang()
    {
        return $this->belongsTo(Jenjang::class);
    }
}
