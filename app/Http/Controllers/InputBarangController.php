<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InputBarangController extends Controller
{
    private const SESSION_KEY = 'inputbarang.items';

    public function html(Request $request)
    {
        $items = $request->session()->get(self::SESSION_KEY, []);

        return view('inputbarang.htmltable', compact('items'));
    }

    public function datatables(Request $request)
    {
        $items = $request->session()->get(self::SESSION_KEY, []);

        return view('inputbarang.datatables', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string'],
            'harga' => ['required', 'string'],
            'redirect_to' => ['required', 'in:html,datatables'],
        ], [
            'nama.required' => 'Nama barang wajib diisi.',
            'harga.required' => 'Harga barang wajib diisi.',
        ]);

        $items = $request->session()->get(self::SESSION_KEY, []);
        $nextId = empty($items) ? 1 : (max(array_column($items, 'id_barang')) + 1);

        $items[] = [
            'id_barang' => $nextId,
            'nama' => $validated['nama'],
            'harga' => $validated['harga'],
        ];

        $request->session()->put(self::SESSION_KEY, $items);

        $targetRoute = $validated['redirect_to'] === 'datatables'
            ? 'inputbarang.datatables'
            : 'inputbarang.html';

        return redirect()->route($targetRoute)->with('success', 'Data barang berhasil ditambahkan.');
    }
}
