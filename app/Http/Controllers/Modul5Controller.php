<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
}
