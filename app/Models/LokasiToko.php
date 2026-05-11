<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LokasiToko extends Model
{
    protected $table = 'lokasi_toko';

    protected $primaryKey = 'barcode';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'barcode',
        'nama_toko',
        'latitude',
        'longitude',
        'accuracy',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy' => 'float',
    ];

    public function kunjungan(): HasMany
    {
        return $this->hasMany(KunjunganToko::class, 'barcode_toko', 'barcode');
    }
}