<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buku extends Model
{
    // legacy table
    protected $table = 'buku';
    protected $primaryKey = 'idbuku';
    public $timestamps = false;

    // accept both legacy and friendly attribute names
    protected $fillable = [
        'kode',
        'judul',
        'pengarang',
        'idkategori',
        'kategori_id',
    ];

    // map 'kategori_id' attribute used in forms/controllers to legacy 'idkategori'
    public function setKategoriIdAttribute($value)
    {
        $this->attributes['idkategori'] = $value;
    }

    public function getKategoriIdAttribute()
    {
        return $this->attributes['idkategori'] ?? null;
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'idkategori');
    }
}
