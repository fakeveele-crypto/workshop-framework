@extends('layouts.app')

@section('title', 'PDF — Laporan & Sertifikat')

@section('content')
  <div class="page-header">
    <h3 class="page-title">
      PDF
    </h3>
    <nav aria-label="breadcrumb">
      <ul class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">
          Pilih laporan atau sertifikat untuk diunduh
        </li>
      </ul>
    </nav>
  </div>

  <div class="row">
    <div class="col-md-6 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title">Laporan Koleksi</h4>
          <p class="card-description">Unduh laporan PDF berisi ringkasan koleksi buku.</p>
          <a href="{{ route('pdf.laporan') }}" class="btn btn-primary">Unduh Laporan (PDF)</a>
        </div>
      </div>
    </div>

    <div class="col-md-6 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title">Sertifikat</h4>
          <p class="card-description">Cetak sertifikat pengguna saat ini dalam format PDF (landscape).</p>
          <a href="{{ route('pdf.sertifikat') }}" class="btn btn-success">Cetak Sertifikat (PDF)</a>
        </div>
      </div>
    </div>
  </div>

@endsection
