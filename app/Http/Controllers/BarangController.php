<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class BarangController extends Controller
{
    public function index()
    {
        if (! Schema::hasTable('barang')) {
            $barangs = collect();
            $columns = [];
            return view('barang.index', compact('barangs', 'columns'));
        }

        $columns = Schema::getColumnListing('barang');
        $barangs = Barang::query()->get();
        return view('barang.index', compact('barangs', 'columns'));
    }

    public function create()
    {
        if (! Schema::hasTable('barang')) {
            return redirect()->route('barang.index')->with('error', 'Tabel barang belum tersedia di database.');
        }

        $columns = Schema::getColumnListing('barang');
        return view('barang.create', compact('columns'));
    }

    public function store(Request $request)
    {
        if (! Schema::hasTable('barang')) {
            return redirect()->route('barang.index')->with('error', 'Tabel barang belum tersedia di database.');
        }

        $columns = Schema::getColumnListing('barang');
        $rules = [
            'nama' => in_array('nama', $columns, true) ? 'required|string|max:255' : 'sometimes',
            'harga' => in_array('harga', $columns, true) ? 'required|numeric' : 'sometimes',
        ];

        if (in_array('timestamp', $columns, true)) {
            $rules['timestamp'] = 'nullable|date';
        }

        $data = $request->validate($rules);

        Barang::create($data);
        return redirect()->route('barang.index')->with('success', 'Data barang berhasil ditambahkan.');
    }

    public function edit($id)
    {
        if (! Schema::hasTable('barang')) {
            return redirect()->route('barang.index')->with('error', 'Tabel barang belum tersedia di database.');
        }

        $barang = Barang::find($id);
        if (! $barang) {
            return redirect()->route('barang.index')->with('error', 'Data barang tidak ditemukan.');
        }

        $columns = Schema::getColumnListing('barang');
        return view('barang.edit', compact('barang', 'columns'));
    }

    public function update(Request $request, $id)
    {
        if (! Schema::hasTable('barang')) {
            return redirect()->route('barang.index')->with('error', 'Tabel barang belum tersedia di database.');
        }

        $barang = Barang::find($id);
        if (! $barang) {
            return redirect()->route('barang.index')->with('error', 'Data barang tidak ditemukan.');
        }

        $columns = Schema::getColumnListing('barang');
        $rules = [
            'nama' => in_array('nama', $columns, true) ? 'required|string|max:255' : 'sometimes',
            'harga' => in_array('harga', $columns, true) ? 'required|numeric' : 'sometimes',
        ];

        if (in_array('timestamp', $columns, true)) {
            $rules['timestamp'] = 'nullable|date';
        }

        $data = $request->validate($rules);

        $barang->update($data);
        return redirect()->route('barang.index')->with('success', 'Data barang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        if (! Schema::hasTable('barang')) {
            return redirect()->route('barang.index')->with('error', 'Tabel barang belum tersedia di database.');
        }

        $barang = Barang::find($id);
        if (! $barang) {
            return redirect()->route('barang.index')->with('error', 'Data barang tidak ditemukan.');
        }

        $barang->delete();
        return redirect()->route('barang.index')->with('success', 'Data barang berhasil dihapus.');
    }
}
