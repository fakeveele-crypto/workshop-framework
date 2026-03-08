<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InputBarangController extends Controller
{
    private const SESSION_KEY = 'inputbarang.items';

    private const REDIRECT_ROUTES = [
        'html' => 'inputbarang.html',
        'datatables' => 'inputbarang.datatables',
        'htmlCrud' => 'inputbarang.html.crud',
        'datatablesCrud' => 'inputbarang.datatables.crud',
    ];

    public function index()
    {
        return view('inputbarang.index');
    }

    public function selectKota()
    {
        return view('inputbarang.selectkota');
    }

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

    public function htmlCrud(Request $request)
    {
        $items = $request->session()->get(self::SESSION_KEY, []);

        return view('inputbarang.htmltableCrud', compact('items'));
    }

    public function datatablesCrud(Request $request)
    {
        $items = $request->session()->get(self::SESSION_KEY, []);

        return view('inputbarang.datatablesCrud', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string'],
            'harga' => ['required', 'string'],
            'redirect_to' => ['required', 'in:html,datatables,htmlCrud,datatablesCrud'],
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

        $targetRoute = $this->resolveTargetRoute($validated['redirect_to']);

        return redirect()->route($targetRoute)->with('success', 'Data barang berhasil ditambahkan.');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id_barang' => ['required', 'integer', 'min:1'],
            'nama' => ['required', 'string'],
            'harga' => ['required', 'string'],
            'redirect_to' => ['required', 'in:html,datatables,htmlCrud,datatablesCrud'],
        ], [
            'id_barang.required' => 'ID barang wajib diisi.',
            'nama.required' => 'Nama barang wajib diisi.',
            'harga.required' => 'Harga barang wajib diisi.',
        ]);

        $items = $request->session()->get(self::SESSION_KEY, []);

        foreach ($items as &$item) {
            if ((int) $item['id_barang'] !== (int) $validated['id_barang']) {
                continue;
            }

            $item['nama'] = $validated['nama'];
            $item['harga'] = $validated['harga'];

            break;
        }
        unset($item);

        $request->session()->put(self::SESSION_KEY, $items);

        return redirect()
            ->route($this->resolveTargetRoute($validated['redirect_to']))
            ->with('success', 'Data barang berhasil diubah.');
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'id_barang' => ['required', 'integer', 'min:1'],
            'redirect_to' => ['required', 'in:html,datatables,htmlCrud,datatablesCrud'],
        ], [
            'id_barang.required' => 'ID barang wajib diisi.',
        ]);

        $items = $request->session()->get(self::SESSION_KEY, []);
        $items = array_values(array_filter($items, static function (array $item) use ($validated) {
            return (int) $item['id_barang'] !== (int) $validated['id_barang'];
        }));

        $request->session()->put(self::SESSION_KEY, $items);

        return redirect()
            ->route($this->resolveTargetRoute($validated['redirect_to']))
            ->with('success', 'Data barang berhasil dihapus.');
    }

    private function resolveTargetRoute(string $redirectTo): string
    {
        return self::REDIRECT_ROUTES[$redirectTo] ?? self::REDIRECT_ROUTES['html'];
    }
}
