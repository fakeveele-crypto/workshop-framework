@extends('layouts.app')

@section('title', 'Riwayat Pemesanan')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Riwayat Pemesanan</h3>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-6">
                            <label for="searchInput" class="form-label fw-bold">Guest ID atau ID Pesanan</label>
                            <input type="text" id="searchInput" class="form-control" value="{{ $searchQuery ?? '' }}" placeholder="Masukkan Guest ID atau ID Pesanan">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" id="searchButton" class="btn btn-primary d-block">Cari Riwayat</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0">Riwayat Pemesanan</h4>
                </div>
                <div class="card-body">
                    @if($orders->isEmpty())
                        <div class="text-center text-muted py-4">
                            @if($searchQuery)
                                Tidak ada riwayat pemesanan untuk: {{ $searchQuery }}
                            @else
                                Masukkan Guest ID atau ID Pesanan untuk melihat riwayat pemesanan.
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID Pesanan</th>
                                        <th>Tanggal</th>
                                        <th>Total</th>
                                        <th>Status Bayar</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>{{ $order->idpesanan }}</td>
                                            <td>{{ $order->timestamp->format('d/m/Y H:i') }}</td>
                                            <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                            <td>
                                                <span class="badge {{ $order->status_bayar ? 'bg-success' : 'bg-warning' }}">
                                                    {{ $order->status_bayar ? 'Sudah Bayar' : 'Belum Bayar' }}
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" onclick="showDetail({{ $order->idpesanan }})">Detail & QR</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Order Detail -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderDetailModalLabel">Detail Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="orderDetailContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        document.getElementById('searchButton').addEventListener('click', function() {
            const searchQuery = document.getElementById('searchInput').value.trim();
            if (searchQuery) {
                window.location.href = '{{ route("customer.riwayat") }}?search=' + encodeURIComponent(searchQuery);
            }
        });

        function showDetail(idpesanan) {
            fetch('{{ route("customer.detail_pesanan", ["idpesanan" => "__ID__"]) }}'.replace('__ID__', idpesanan))
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Gagal memuat detail pesanan');
                    }

                    const orderData = data.data;
                    let content = `
                        <div class="text-center mb-4">
                            <h6>QR Code Pesanan</h6>
                            <div id="qrcode-${orderData.order_id}"></div>
                            <p class="mt-2"><strong>ID Pesanan:</strong> ${orderData.order_id}</p>
                            <p><strong>Status:</strong> <span class="badge ${orderData.status_bayar ? 'bg-success' : 'bg-danger'}">${orderData.status_bayar ? 'Sudah Bayar' : 'Belum Bayar'}</span></p>
                        </div>
                        <hr>
                        <div>
                            <h6>Menu yang Dipesan</h6>
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Menu</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    orderData.menus.forEach(menu => {
                        content += `
                            <tr>
                                <td>${menu.nama_menu}</td>
                                <td class="text-center">${menu.jumlah}</td>
                                <td class="text-end">Rp ${menu.harga.toLocaleString('id-ID')}</td>
                                <td class="text-end">Rp ${menu.subtotal.toLocaleString('id-ID')}</td>
                            </tr>
                        `;
                    });

                    content += `
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3" class="text-end">Total</th>
                                        <th class="text-end">Rp ${orderData.total.toLocaleString('id-ID')}</th>
                                    </tr>
                                </tfoot>
                            </table>
                            <p class="text-muted"><small>Tanggal: ${orderData.timestamp}</small></p>
                        </div>
                    `;

                    document.getElementById('orderDetailContent').innerHTML = content;

                    // Generate QR code
                    new QRCode(document.getElementById(`qrcode-${orderData.order_id}`), {
                        text: String(orderData.order_id),
                        width: 128,
                        height: 128,
                    });

                    new bootstrap.Modal(document.getElementById('orderDetailModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('orderDetailContent').innerHTML = '<p class="text-danger">Gagal memuat detail pesanan: ' + error.message + '</p>';
                    new bootstrap.Modal(document.getElementById('orderDetailModal')).show();
                });
        }
    </script>
@endpush