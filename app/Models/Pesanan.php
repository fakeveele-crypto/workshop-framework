<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pesanan extends Model
{
    protected $table = 'pesanan';

    protected $primaryKey = 'idpesanan';

    public $timestamps = false;

    protected $fillable = [
        'iduser',
        'nama',
        'timestamp',
        'total',
        'metode_bayar',
        'status_bayar',
        'external_id',
        'snap_token',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'total' => 'integer',
        'metode_bayar' => 'integer',
        'status_bayar' => 'integer',
    ];

    public function detail_pesanan(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'idpesanan', 'idpesanan');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'iduser', 'id');
    }
}
