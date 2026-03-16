<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Throwable;

class Modul5Controller extends Controller
{
    private const DATA_SOURCE_BASE_URL = 'https://raw.githubusercontent.com/guzfirdaus/Wilayah-Administrasi-Indonesia/master/csv/';

    private const CACHE_MINUTES = 1440;

    public function index()
    {
        return view('modul5.index');
    }

    public function pilihanWilayah()
    {
        return redirect()->route('modul5.pilihanwilayah.ajax');
    }

    public function pilihanWilayahAjax()
    {
        return view('modul5.wilayahAjax');
    }

    public function pilihanWilayahAxios()
    {
        return view('modul5.wilayahAxios');
    }

    public function pos()
    {
        return redirect()->route('modul5.pos.ajax');
    }

    public function posAjax()
    {
        return view('modul5.posAjax');
    }

    public function posAxios()
    {
        return view('modul5.posAxios');
    }

    public function findBarangByKode(Request $request): JsonResponse
    {
        if (! Schema::hasTable('barang')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel barang belum tersedia.',
            ], 422);
        }

        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:255'],
        ]);

        $columns = $this->resolveBarangColumns();

        if ($columns['kode'] === null || $columns['nama'] === null || $columns['harga'] === null) {
            return response()->json([
                'success' => false,
                'message' => 'Kolom barang (kode/nama/harga) belum lengkap.',
            ], 500);
        }

        $kode = trim((string) $validated['kode']);

        $barang = DB::table('barang')
            ->select([
                $columns['kode'].' as kode_barang',
                $columns['nama'].' as nama_barang',
                $columns['harga'].' as harga_barang',
            ])
            ->where($columns['kode'], $kode)
            ->first();

        if (! $barang) {
            return response()->json([
                'success' => false,
                'message' => 'Kode barang tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'kode' => (string) $barang->kode_barang,
                'nama' => (string) $barang->nama_barang,
                'harga' => (float) $barang->harga_barang,
            ],
        ]);
    }

    public function checkoutPos(Request $request): JsonResponse
    {
        if (! Schema::hasTable('penjualan') || ! Schema::hasTable('penjualan_detail')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel penjualan dan penjualan_detail belum tersedia di database.',
            ], 422);
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.kode' => ['required', 'string', 'max:255'],
            'items.*.nama' => ['required', 'string', 'max:255'],
            'items.*.harga' => ['required', 'numeric', 'min:0'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'items.*.subtotal' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
        ]);

        $items = collect($validated['items'])->map(function (array $item) {
            $harga = (float) $item['harga'];
            $jumlah = (int) $item['jumlah'];
            $subtotal = (float) $item['subtotal'];

            return [
                'kode' => trim((string) $item['kode']),
                'nama' => trim((string) $item['nama']),
                'harga' => $harga,
                'jumlah' => $jumlah,
                'subtotal' => $subtotal,
            ];
        });

        $calculatedTotal = (float) $items->sum('subtotal');
        $requestTotal = (float) $validated['total'];

        if (abs($calculatedTotal - $requestTotal) > 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'Total transaksi tidak valid.',
            ], 422);
        }

        $penjualanColumns = Schema::getColumnListing('penjualan');
        $detailColumns = Schema::getColumnListing('penjualan_detail');

        $penjualanIdColumn = $this->firstExistingColumn($penjualanColumns, ['id_penjualan', 'id']);
        $detailPenjualanIdColumn = $this->firstExistingColumn($detailColumns, ['id_penjualan', 'penjualan_id']);
        $detailKodeColumn = $this->firstExistingColumn($detailColumns, ['id_barang', 'kode_barang', 'kode']);
        $detailNamaColumn = $this->firstExistingColumn($detailColumns, ['nama_barang', 'nama']);
        $detailHargaColumn = $this->firstExistingColumn($detailColumns, ['harga', 'harga_barang']);
        $detailJumlahColumn = $this->firstExistingColumn($detailColumns, ['jumlah', 'qty']);
        $detailSubtotalColumn = $this->firstExistingColumn($detailColumns, ['subtotal']);

        if (
            $penjualanIdColumn === null
            || $detailPenjualanIdColumn === null
            || $detailKodeColumn === null
            || $detailJumlahColumn === null
            || $detailSubtotalColumn === null
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Struktur tabel penjualan belum sesuai untuk proses POS.',
            ], 500);
        }

        $now = now();

        try {
            $result = DB::transaction(function () use (
                $penjualanColumns,
                $detailColumns,
                $penjualanIdColumn,
                $detailPenjualanIdColumn,
                $detailKodeColumn,
                $detailNamaColumn,
                $detailHargaColumn,
                $detailJumlahColumn,
                $detailSubtotalColumn,
                $items,
                $requestTotal,
                $now
            ) {
                $penjualanData = [];

                if (in_array('total', $penjualanColumns, true)) {
                    $penjualanData['total'] = $requestTotal;
                }

                if (in_array('timestamp', $penjualanColumns, true)) {
                    $penjualanData['timestamp'] = $now;
                }

                if (in_array('created_at', $penjualanColumns, true)) {
                    $penjualanData['created_at'] = $now;
                }

                if (in_array('updated_at', $penjualanColumns, true)) {
                    $penjualanData['updated_at'] = $now;
                }

                if (empty($penjualanData)) {
                    throw new \RuntimeException('Data header penjualan tidak dapat disimpan.');
                }

                $penjualanId = DB::table('penjualan')->insertGetId($penjualanData, $penjualanIdColumn);

                $detailRows = $items->map(function (array $item) use (
                    $detailColumns,
                    $detailPenjualanIdColumn,
                    $detailKodeColumn,
                    $detailNamaColumn,
                    $detailHargaColumn,
                    $detailJumlahColumn,
                    $detailSubtotalColumn,
                    $penjualanId,
                    $now
                ) {
                    $row = [
                        $detailPenjualanIdColumn => $penjualanId,
                        $detailKodeColumn => $item['kode'],
                    ];

                    if ($detailNamaColumn !== null) {
                        $row[$detailNamaColumn] = $item['nama'];
                    }

                    if ($detailHargaColumn !== null) {
                        $row[$detailHargaColumn] = $item['harga'];
                    }

                    if ($detailJumlahColumn !== null) {
                        $row[$detailJumlahColumn] = $item['jumlah'];
                    }

                    if ($detailSubtotalColumn !== null) {
                        $row[$detailSubtotalColumn] = $item['subtotal'];
                    }

                    if (in_array('timestamp', $detailColumns, true)) {
                        $row['timestamp'] = $now;
                    }

                    if (in_array('created_at', $detailColumns, true)) {
                        $row['created_at'] = $now;
                    }

                    if (in_array('updated_at', $detailColumns, true)) {
                        $row['updated_at'] = $now;
                    }

                    return $row;
                })->values()->all();

                DB::table('penjualan_detail')->insert($detailRows);

                return [
                    'id_penjualan' => $penjualanId,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran transaksi berhasil disimpan.',
                'data' => $result,
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi pembayaran.',
            ], 500);
        }
    }

    public function provinces(): JsonResponse
    {
        $provinces = $this->loadCsv('provinces.csv');

        return response()->json($this->mapOptions($provinces));
    }

    public function regencies(Request $request): JsonResponse
    {
        $provinceId = (string) $request->query('province_id', '0');

        if ($provinceId === '0' || $provinceId === '') {
            return response()->json([]);
        }

        $regencies = array_values(array_filter($this->loadCsv('regencies.csv'), static function (array $row) use ($provinceId) {
            return (string) ($row['province_id'] ?? '') === $provinceId;
        }));

        return response()->json($this->mapOptions($regencies));
    }

    public function districts(Request $request): JsonResponse
    {
        $regencyId = (string) $request->query('regency_id', '0');

        if ($regencyId === '0' || $regencyId === '') {
            return response()->json([]);
        }

        $districts = array_values(array_filter($this->loadCsv('districts.csv'), static function (array $row) use ($regencyId) {
            return (string) ($row['regency_id'] ?? '') === $regencyId;
        }));

        return response()->json($this->mapOptions($districts));
    }

    public function villages(Request $request): JsonResponse
    {
        $districtId = (string) $request->query('district_id', '0');

        if ($districtId === '0' || $districtId === '') {
            return response()->json([]);
        }

        $villages = array_values(array_filter($this->loadCsv('villages.csv'), static function (array $row) use ($districtId) {
            return (string) ($row['district_id'] ?? '') === $districtId;
        }));

        return response()->json($this->mapOptions($villages));
    }

    private function loadCsv(string $filename): array
    {
        return Cache::remember("modul5.wilayah.{$filename}", now()->addMinutes(self::CACHE_MINUTES), function () use ($filename) {
            $response = Http::timeout(120)->get(self::DATA_SOURCE_BASE_URL.$filename);

            if (! $response->successful()) {
                return [];
            }

            return $this->parseCsv($response->body());
        });
    }

    private function parseCsv(string $content): array
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            return [];
        }

        fwrite($stream, $content);
        rewind($stream);

        $headers = null;
        $rows = [];

        while (($data = fgetcsv($stream, 0, ';')) !== false) {
            if ($data === [null] || $data === []) {
                continue;
            }

            if ($headers === null) {
                $headers = array_map(static function ($value) {
                    return trim((string) $value, "\xEF\xBB\xBF \t\n\r\0\x0B");
                }, $data);

                continue;
            }

            if (count($data) !== count($headers)) {
                continue;
            }

            $row = [];

            foreach ($headers as $index => $header) {
                $row[$header] = trim((string) ($data[$index] ?? ''));
            }

            if (($row['id'] ?? '') === '') {
                continue;
            }

            $rows[] = $row;
        }

        fclose($stream);

        return $rows;
    }

    private function mapOptions(array $rows): array
    {
        return array_values(array_map(function (array $row) {
            $name = (string) ($row['name'] ?? '');
            $cleanName = preg_replace('/\s+/u', ' ', trim($name));

            return [
                'id' => (string) ($row['id'] ?? ''),
                'name' => $cleanName ?? trim($name),
            ];
        }, $rows));
    }

    private function resolveBarangColumns(): array
    {
        $columns = Schema::getColumnListing('barang');

        return [
            'kode' => $this->firstExistingColumn($columns, ['kode', 'id_barang', 'kode_barang']),
            'nama' => $this->firstExistingColumn($columns, ['nama', 'nama_barang']),
            'harga' => $this->firstExistingColumn($columns, ['harga', 'harga_barang']),
        ];
    }

    private function firstExistingColumn(array $columns, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return null;
    }
}
