<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use App\Models\Buku;

class DashboardController extends Controller
{
    public function index()
    {
        // default empty values for guests
        $kategoriCount = 0;
        $bukuCount = 0;
        $recentKategoris = collect();
        $recentBukus = collect();

        // only load data if the user is authenticated
        if (auth()->check()) {
            try {
                $kategoriCount = Kategori::count();
                $bukuCount = Buku::count();
                // legacy primary keys: idkategori and idbuku
                $recentKategoris = Kategori::orderBy('idkategori','desc')->take(5)->get();
                $recentBukus = Buku::with('kategori')->orderBy('idbuku','desc')->take(5)->get();
            } catch (\Throwable $e) {
                // keep defaults on error
            }
        }

        return view('dashboard', compact('kategoriCount','bukuCount','recentKategoris','recentBukus'));
    }
}
