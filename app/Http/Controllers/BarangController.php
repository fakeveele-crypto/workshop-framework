<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

        $data = $request->validate($rules);

        if (in_array('timestamp', $columns, true)) {
            $data['timestamp'] = Carbon::now();
        }

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

    public function printLabels(Request $request)
    {
        if (! Schema::hasTable('barang')) {
            return redirect()->route('barang.index')->with('error', 'Tabel barang belum tersedia di database.');
        }

        $validated = $request->validate([
            'selected_barang' => ['required', 'array', 'min:1'],
            'selected_barang.*' => ['required', 'integer'],
            'x' => ['required', 'integer', 'between:1,5'],
            'y' => ['required', 'integer', 'between:1,8'],
        ], [
            'selected_barang.required' => 'Pilih minimal 1 barang untuk dicetak.',
            'x.between' => 'Nilai X harus antara 1 sampai 5.',
            'y.between' => 'Nilai Y harus antara 1 sampai 8.',
        ]);

        $ids = collect($validated['selected_barang'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $keyName = (new Barang())->getKeyName();

        $barangs = Barang::query()
            ->whereIn($keyName, $ids->all())
            ->get()
            ->sortBy(fn ($barang) => $ids->search((int) $barang->getKey()))
            ->values();

        if ($barangs->isEmpty()) {
            return redirect()->route('barang.index')->with('error', 'Data barang yang dipilih tidak ditemukan.');
        }

        $x = (int) $validated['x'];
        $y = (int) $validated['y'];
        $startIndex = (($y - 1) * 5) + ($x - 1);

        $cells = array_fill(0, 40, null);
        foreach ($barangs as $offset => $barang) {
            $cellIndex = $startIndex + $offset;
            if ($cellIndex >= 40) {
                break;
            }
            $cells[$cellIndex] = $barang;
        }

        $paperWidthMm = 210;
        $paperHeightMm = 210;
        $mmToPt = 72 / 25.4;

        $customPaper = [
            0,
            0,
            $paperWidthMm * $mmToPt,
            $paperHeightMm * $mmToPt,
        ];

        $pdf = Pdf::loadView('barang.label-pdf', [
            'cells' => $cells,
            'x' => $x,
            'y' => $y,
            'selectedCount' => $barangs->count(),
        ])->setPaper($customPaper, 'portrait');

        return $pdf->stream('Label_Barang_' . now()->format('Ymd_His') . '.pdf');
    }
}
