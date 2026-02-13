<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    // legacy table
    protected $table = 'kategori';
    protected $primaryKey = 'idkategori';
    public $timestamps = false;

    // allow both legacy and friendly attribute names for mass assignment
    protected $fillable = [
        'nama_kategori',
        'nama',
    ];

    // accessor to read $kategori->nama
    public function getNamaAttribute()
    {
        return $this->attributes['nama_kategori'] ?? null;
    }

    // mutator to set $kategori->nama and map to nama_kategori
    public function setNamaAttribute($value)
    {
        $this->attributes['nama_kategori'] = $value;
    }

    public function bukus()
    {
        return $this->hasMany(Buku::class, 'idkategori');
    }
}
