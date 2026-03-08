<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KategoriController extends Controller
{
    public function index()
    {
        if (! Schema::hasTable('kategori')) {
            $kategoris = collect();
            return view('kategori.index', compact('kategoris'));
        }

        $kategoris = Kategori::all();
        return view('kategori.index', compact('kategoris'));
    }

    public function create()
    {
        return view('kategori.create');
    }

    public function store(Request $request)
    {
        if (! Schema::hasTable('kategori')) {
            return redirect()->route('kategori.index')->with('error', 'Tabel kategori belum tersedia.');
        }

        $data = $request->validate([ 'nama' => 'required|string|max:255' ]);

        try {
            Kategori::create($data);
        } catch (UniqueConstraintViolationException $exception) {
            if (! $this->isKategoriPrimaryKeyConflict($exception)) {
                throw $exception;
            }

            $this->syncKategoriPrimaryKeySequence();
            Kategori::create($data);
        }

        return redirect()->route('kategori.index')->with('success', 'Kategori ditambahkan.');
    }

    public function edit($id)
    {
        if (! Schema::hasTable('kategori')) {
            return redirect()->route('kategori.index')->with('error', 'Tabel kategori belum tersedia.');
        }

        $kategori = Kategori::find($id);
        if (! $kategori) {
            return redirect()->route('kategori.index')->with('error', 'Kategori tidak ditemukan.');
        }

        return view('kategori.edit', compact('kategori'));
    }

    public function update(Request $request, $id)
    {
        if (! Schema::hasTable('kategori')) {
            return redirect()->route('kategori.index')->with('error', 'Tabel kategori belum tersedia.');
        }

        $kategori = Kategori::find($id);
        if (! $kategori) {
            return redirect()->route('kategori.index')->with('error', 'Kategori tidak ditemukan.');
        }

        $data = $request->validate([ 'nama' => 'required|string|max:255' ]);
        $kategori->update($data);
        return redirect()->route('kategori.index')->with('success', 'Kategori diperbarui.');
    }

    public function destroy($id)
    {
        if (! Schema::hasTable('kategori')) {
            return redirect()->route('kategori.index')->with('error', 'Tabel kategori belum tersedia.');
        }

        $kategori = Kategori::find($id);
        if (! $kategori) {
            return redirect()->route('kategori.index')->with('error', 'Kategori tidak ditemukan.');
        }

        $kategori->delete();
        return redirect()->route('kategori.index')->with('success', 'Kategori dihapus.');
    }

    private function isKategoriPrimaryKeyConflict(UniqueConstraintViolationException $exception): bool
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return false;
        }

        $message = strtolower($exception->getMessage());
        return str_contains($message, 'kategori_pkey') || str_contains($message, '(idkategori)');
    }

    private function syncKategoriPrimaryKeySequence(): void
    {
        DB::statement("SELECT setval(pg_get_serial_sequence('kategori', 'idkategori'), COALESCE((SELECT MAX(idkategori) FROM kategori), 0) + 1, false)");
    }
}
