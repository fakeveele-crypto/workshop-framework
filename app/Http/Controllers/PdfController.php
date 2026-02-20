<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class PdfController extends Controller
{
    public function cetakSertifikat()
    {
        $user = Auth::user();

        $data = [
            'nama' => $user->name,
            'email' => $user->email,
            'judul' => 'Sertifikat Apresiasi Pembaca',
            'keterangan' => 'Telah berkontribusi aktif dalam mengelola sistem Koleksi Buku.',
            'tanggal' => date('d F Y')
        ];

        $pdf = Pdf::loadView('pdf.sertifikat', $data)->setPaper('a4', 'landscape');

        return $pdf->stream('Sertifikat_' . $user->name . '.pdf');
    }

    public function cetakLaporan()
    {
        $user = Auth::user();
        
        $koleksi = [
            ['judul' => 'Belajar Laravel 11', 'kategori' => 'Programming'],
            ['judul' => 'Sistem Informasi Perpustakaan', 'kategori' => 'E-Book'],
            ['judul' => 'Panduan Database PostgreSQL', 'kategori' => 'Database'],
        ];

        $data = [
            'nama_user' => $user->name,
            'koleksi' => $koleksi
        ];

        $pdf = Pdf::loadView('pdf.laporan', $data)->setPaper('a4', 'portrait');
        
        return $pdf->download('Laporan_Koleksi_' . $user->name . '.pdf');
    }
}