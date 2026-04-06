@extends('layouts.app')

@section('title', 'Kantin Vendor - Pesanan Lunas')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Monitoring Pesanan Lunas</h3>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('vendor.index') }}" class="btn btn-outline-primary">Manajemen Master Menu</a>
                <a href="{{ route('vendor.orders') }}" class="btn btn-success">Daftar Pesanan Lunas</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Daftar Pesanan (Status: Lunas)</h4>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Pembeli</th>
                            <th>Waktu Pesan</th>
                            <th>Rincian Menu</th>
                            <th>Total Bayar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $index => $order)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $order->nama }}</td>
                                <td>{{ optional($order->timestamp)->format('d-m-Y H:i:s') ?? '-' }}</td>
                                <td>
                                    <ul class="mb-0 ps-3">
                                        @forelse($order->detail_pesanan as $detail)
                                            <li>
                                                {{ $detail->menu->nama_menu ?? 'Menu tidak ditemukan' }}
                                                ({{ $detail->jumlah }} x Rp {{ number_format($detail->harga, 0, ',', '.') }})
                                                = Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                            </li>
                                        @empty
                                            <li>Tidak ada detail menu.</li>
                                        @endforelse
                                    </ul>
                                </td>
                                <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                <td><span class="badge badge-success">Lunas</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada pesanan dengan status lunas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
