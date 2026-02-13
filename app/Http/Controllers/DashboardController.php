<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use App\Models\Buku;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $kategoriCount = Kategori::count();
            $bukuCount = Buku::count();
            // legacy primary keys: idkategori and idbuku
            $recentKategoris = Kategori::orderBy('idkategori','desc')->take(5)->get();
            $recentBukus = Buku::with('kategori')->orderBy('idbuku','desc')->take(5)->get();
        } catch (\Throwable $e) {
            $kategoriCount = 0;
            $bukuCount = 0;
            $recentKategoris = collect();
            $recentBukus = collect();
        }

        return view('dashboard', compact('kategoriCount','bukuCount','recentKategoris','recentBukus'));
    }
}
