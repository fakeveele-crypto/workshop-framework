<?php

namespace App\Http\Controllers;

use App\Models\KunjunganToko;
use App\Models\LokasiToko;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class KunjunganTokoController extends Controller
{
    private const BASE_THRESHOLD_METERS = 300;

    public function index()
    {
        $this->ensureTables();

        $tokoList = LokasiToko::query()
            ->orderBy('barcode')
            ->get()
            ->map(function (LokasiToko $toko) {
                $toko->qr_svg = QrCode::format('svg')->size(90)->margin(1)->generate($toko->barcode);

                return $toko;
            });

        return view('kunjungan_toko.index', [
            'tokoList' => $tokoList,
        ]);
    }

    public function create()
    {
        $this->ensureTables();

        return view('kunjungan_toko.create', [
            'nextBarcode' => $this->generateNextBarcode(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureTables();

        $validated = $request->validate([
            'barcode' => [
                'required',
                'string',
                'max:8',
                'regex:/^TKO-\d{3}$/',
                Rule::unique('lokasi_toko', 'barcode'),
            ],
            'nama_toko' => ['required', 'string', 'max:50'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'accuracy' => ['required', 'numeric', 'min:0'],
        ]);

        LokasiToko::create([
            'barcode' => trim((string) $validated['barcode']),
            'nama_toko' => trim((string) $validated['nama_toko']),
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
            'accuracy' => (float) $validated['accuracy'],
        ]);

        return redirect()
            ->route('kunjungan_toko.index')
            ->with('success', 'Toko berhasil ditambahkan.');
    }

    public function scan()
    {
        $this->ensureTables();

        return view('kunjungan_toko.scan');
    }

    public function processScan(Request $request): JsonResponse
    {
        $this->ensureTables();

        $validated = $request->validate([
            'barcode_toko' => ['required', 'string', 'exists:lokasi_toko,barcode'],
            'lat_sales' => ['required', 'numeric'],
            'lng_sales' => ['required', 'numeric'],
            'acc_sales' => ['required', 'numeric', 'min:0'],
        ]);

        $toko = LokasiToko::query()->where('barcode', $validated['barcode_toko'])->firstOrFail();

        $jarakAktual = $this->haversineDistance(
            (float) $toko->latitude,
            (float) $toko->longitude,
            (float) $validated['lat_sales'],
            (float) $validated['lng_sales'],
        );

        $ambangBatas = self::BASE_THRESHOLD_METERS;
        $thresholdEfektif = $ambangBatas + (float) $toko->accuracy + (float) $validated['acc_sales'];
        $status = $jarakAktual <= $thresholdEfektif ? '1' : '2';

        $kunjungan = KunjunganToko::create([
            'barcode_toko' => $toko->barcode,
            'lat_sales' => (float) $validated['lat_sales'],
            'lng_sales' => (float) $validated['lng_sales'],
            'acc_sales' => (float) $validated['acc_sales'],
            'jarak_aktual' => $jarakAktual,
            'status' => $status,
            'waktu_kunjungan' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $status === '1' ? 'DITERIMA' : 'DITOLAK',
            'data' => [
                'kunjungan_id' => $kunjungan->id,
                'barcode_toko' => $toko->barcode,
                'nama_toko' => $toko->nama_toko,
                'lat_toko' => (float) $toko->latitude,
                'lng_toko' => (float) $toko->longitude,
                'acc_toko' => (float) $toko->accuracy,
                'lat_sales' => (float) $validated['lat_sales'],
                'lng_sales' => (float) $validated['lng_sales'],
                'acc_sales' => (float) $validated['acc_sales'],
                'jarak_aktual' => round($jarakAktual, 2),
                'ambang_batas' => $ambangBatas,
                'threshold_efektif' => round($thresholdEfektif, 2),
                'status' => $status,
                'status_label' => $status === '1' ? 'DITERIMA' : 'DITOLAK',
                'waktu_kunjungan' => optional($kunjungan->waktu_kunjungan)->format('d-m-Y H:i:s'),
            ],
        ]);
    }

    public function history()
    {
        $this->ensureTables();

        $history = KunjunganToko::query()
            ->with('toko')
            ->orderByDesc('waktu_kunjungan')
            ->orderByDesc('id')
            ->get();

        return view('kunjungan_toko.history', [
            'history' => $history,
        ]);
    }

    protected function ensureTables(): void
    {
        abort_unless(Schema::hasTable('lokasi_toko') && Schema::hasTable('kunjungan_toko'), 500, 'Tabel lokasi_toko atau kunjungan_toko belum tersedia.');
    }

    protected function generateNextBarcode(): string
    {
        $lastNumber = LokasiToko::query()
            ->where('barcode', 'like', 'TKO-%')
            ->pluck('barcode')
            ->map(function (string $barcode) {
                $suffix = substr($barcode, 4);

                return ctype_digit($suffix) ? (int) $suffix : 0;
            })
            ->max() ?? 0;

        return sprintf('TKO-%03d', $lastNumber + 1);
    }

    protected function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $radius = 6371000;
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);
        $a = sin($deltaLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($deltaLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $radius * $c;
    }
}