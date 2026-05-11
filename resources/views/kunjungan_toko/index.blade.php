@extends('layouts.app')

@section('title', 'Kunjungan Toko')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="page-title mb-1">Kunjungan Toko</h3>
            <p class="text-muted mb-0">Kelola data lokasi toko dan akses fitur scan kunjungan.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('kunjungan_toko.create') }}" class="btn btn-primary">Tambah Toko</a>
            <a href="{{ route('kunjungan_toko.scan') }}" class="btn btn-success">Scan Barcode</a>
            <a href="{{ route('kunjungan_toko.history') }}" class="btn btn-outline-secondary">History Scan</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px;">Barcode</th>
                            <th style="width: 120px;">QR Code</th>
                            <th>Nama Toko</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Accuracy</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tokoList as $toko)
                            <tr>
                                <td class="fw-semibold">{{ $toko->barcode }}</td>
                                <td>{!! $toko->qr_svg !!}</td>
                                <td>{{ $toko->nama_toko }}</td>
                                <td>{{ $toko->latitude }}</td>
                                <td>{{ $toko->longitude }}</td>
                                <td>{{ $toko->accuracy }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada data toko.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection