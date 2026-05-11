<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganToko extends Model
{
    protected $table = 'kunjungan_toko';

    public $timestamps = false;

    protected $fillable = [
        'barcode_toko',
        'lat_sales',
        'lng_sales',
        'acc_sales',
        'jarak_aktual',
        'status',
        'waktu_kunjungan',
    ];

    protected $casts = [
        'lat_sales' => 'float',
        'lng_sales' => 'float',
        'acc_sales' => 'float',
        'jarak_aktual' => 'float',
        'waktu_kunjungan' => 'datetime',
    ];

    public function toko(): BelongsTo
    {
        return $this->belongsTo(LokasiToko::class, 'barcode_toko', 'barcode');
    }
}