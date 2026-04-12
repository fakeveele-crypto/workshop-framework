<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer';

    protected $fillable = [
        'nama',
        'alamat',
        'provinsi',
        'kota',
        'kecamatan',
        'kodepos_kelurahan',
        'foto_blob',
        'foto_path',
    ];

    protected $casts = [
        'id' => 'integer',
    ];
}
