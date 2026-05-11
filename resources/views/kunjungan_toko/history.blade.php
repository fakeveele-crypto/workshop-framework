@extends('layouts.app')

@section('title', 'History Scan Kunjungan')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="page-title mb-1">History Scan</h3>
            <p class="text-muted mb-0">Riwayat kunjungan toko berdasarkan tabel kunjungan_toko.</p>
        </div>
        <a href="{{ route('kunjungan_toko.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Barcode</th>
                            <th>Nama Toko</th>
                            <th>Lat Sales</th>
                            <th>Lng Sales</th>
                            <th>Acc Sales</th>
                            <th>Jarak Aktual</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($history as $item)
                            <tr>
                                <td>{{ optional($item->waktu_kunjungan)->format('d-m-Y H:i:s') ?? '-' }}</td>
                                <td>{{ $item->barcode_toko }}</td>
                                <td>{{ $item->toko?->nama_toko ?? '-' }}</td>
                                <td>{{ $item->lat_sales }}</td>
                                <td>{{ $item->lng_sales }}</td>
                                <td>{{ $item->acc_sales }}</td>
                                <td>{{ number_format((float) $item->jarak_aktual, 2, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $item->status === '1' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $item->status === '1' ? 'DITERIMA' : 'DITOLAK' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Belum ada history scan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection