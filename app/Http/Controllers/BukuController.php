<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BukuController extends Controller
{
    private function syncBukuSequenceIfNeeded(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql' || ! Schema::hasTable('buku')) {
            return;
        }

        DB::statement("SELECT setval(pg_get_serial_sequence('buku', 'idbuku'), COALESCE((SELECT MAX(idbuku) FROM buku), 0) + 1, false)");
    }

    public function index()
    {
        if (! Schema::hasTable('buku')) {
            $bukus = collect();
            return view('buku.index', compact('bukus'));
        }

        $bukus = Buku::with('kategori')->get();
        return view('buku.index', compact('bukus'));
    }

    public function create()
    {
        $kategoris = Schema::hasTable('kategori') ? Kategori::all() : collect();
        return view('buku.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        if (! Schema::hasTable('buku')) {
            return redirect()->route('buku.index')->with('error', 'Tabel buku belum tersedia.');
        }

        $this->syncBukuSequenceIfNeeded();

        $data = $request->validate([
            'kode' => 'required|string|max:20',
            'judul' => 'required|string|max:500',
            'pengarang' => 'required|string|max:200',
            'kategori_id' => 'required|integer|exists:kategori,idkategori'
        ], [
            'kode.required' => 'Kode wajib diisi.',
            'judul.required' => 'Judul wajib diisi.',
            'pengarang.required' => 'Pengarang wajib diisi.',
            'kategori_id.required' => 'Kategori wajib dipilih.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid.'
        ]);

        Buku::create($data);
        return redirect()->route('buku.index')->with('success', 'Buku ditambahkan.');
    }

    public function edit($id)
    {
        if (! Schema::hasTable('buku')) {
            return redirect()->route('buku.index')->with('error', 'Tabel buku belum tersedia.');
        }

        $buku = Buku::find($id);
        if (! $buku) {
            return redirect()->route('buku.index')->with('error', 'Buku tidak ditemukan.');
        }

        $kategoris = Schema::hasTable('kategori') ? Kategori::all() : collect();
        return view('buku.edit', compact('buku','kategoris'));
    }

    public function update(Request $request, $id)
    {
        if (! Schema::hasTable('buku')) {
            return redirect()->route('buku.index')->with('error', 'Tabel buku belum tersedia.');
        }

        $buku = Buku::find($id);
        if (! $buku) {
            return redirect()->route('buku.index')->with('error', 'Buku tidak ditemukan.');
        }
        $data = $request->validate([
            'kode' => 'required|string|max:20',
            'judul' => 'required|string|max:500',
            'pengarang' => 'required|string|max:200',
            'kategori_id' => 'required|integer|exists:kategori,idkategori'
        ], [
            'kode.required' => 'Kode wajib diisi.',
            'judul.required' => 'Judul wajib diisi.',
            'pengarang.required' => 'Pengarang wajib diisi.',
            'kategori_id.required' => 'Kategori wajib dipilih.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid.'
        ]);

        $buku->update($data);
        return redirect()->route('buku.index')->with('success', 'Buku diperbarui.');
    }

    public function destroy($id)
    {
        if (! Schema::hasTable('buku')) {
            return redirect()->route('buku.index')->with('error', 'Tabel buku belum tersedia.');
        }

        $buku = Buku::find($id);
        if (! $buku) {
            return redirect()->route('buku.index')->with('error', 'Buku tidak ditemukan.');
        }

        $buku->delete();
        return redirect()->route('buku.index')->with('success', 'Buku dihapus.');
    }
}
