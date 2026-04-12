@extends('layouts.app')

@section('title', 'Data Customer')

@section('content')
    <div class="page-header">
        <div>
            <h3 class="page-title mb-1">Data Customer</h3>
            <p class="text-muted mb-0">Daftar customer beserta hasil penyimpanan foto BYTEA dan file path.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 customer-card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('customer_data.create_blob') }}" class="btn btn-primary">Tambah Customer 1</a>
                    <a href="{{ route('customer_data.create_path') }}" class="btn btn-outline-primary">Tambah Customer 2</a>
                </div>
                <span class="badge bg-light text-dark border px-3 py-2">Total: {{ $customers->count() }} customer</span>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive customer-table-wrap">
                <table class="table table-hover align-middle mb-0 customer-table">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Provinsi</th>
                            <th>Kota</th>
                            <th>Kecamatan</th>
                            <th>Kodepos - Kelurahan</th>
                            <th class="text-center">Foto Blob</th>
                            <th class="text-center">Foto Path</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            <tr>
                                <td class="text-center text-muted fw-semibold">{{ $loop->iteration }}</td>
                                <td class="fw-semibold text-dark">{{ $customer->nama }}</td>
                                <td>
                                    <div class="cell-clip" title="{{ $customer->alamat }}">{{ $customer->alamat }}</div>
                                </td>
                                <td>{{ $customer->provinsi }}</td>
                                <td>{{ $customer->kota }}</td>
                                <td>{{ $customer->kecamatan }}</td>
                                <td>{{ $customer->kodepos_kelurahan }}</td>
                                <td class="text-center">
                                    @if ($customer->foto_blob_preview)
                                        <img src="{{ $customer->foto_blob_preview }}" alt="Foto blob {{ $customer->nama }}" class="customer-thumb">
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($customer->foto_path_url)
                                        <img src="{{ $customer->foto_path_url }}" alt="Foto path {{ $customer->nama }}" class="customer-thumb">
                                    @elseif ($customer->foto_path)
                                        <span class="text-muted small">File tidak ditemukan</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Belum ada data customer.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .customer-card {
            border-radius: 14px;
        }

        .customer-table-wrap {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            overflow-x: auto;
            overflow-y: hidden;
            background: #fff;
        }

        .customer-table thead th {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #6c757d;
            border-bottom-width: 1px;
            background: #f8f9fb;
            white-space: nowrap;
        }

        .customer-table tbody td {
            vertical-align: middle;
            border-color: #f1f3f7;
            padding-top: 0.65rem;
            padding-bottom: 0.65rem;
        }

        .customer-table tbody tr:hover {
            background: #fafbff;
        }

        .customer-thumb {
            width: 58px;
            height: 58px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #ffffff;
            box-shadow: 0 0 0 1px #e6e8ef;
            display: inline-block;
        }

        .customer-table th:nth-child(1),
        .customer-table td:nth-child(1) {
            width: 62px;
        }

        .customer-table th:nth-child(8),
        .customer-table td:nth-child(8),
        .customer-table th:nth-child(9),
        .customer-table td:nth-child(9) {
            width: 110px;
            min-width: 110px;
        }

        .cell-clip {
            max-width: 220px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 768px) {
            .customer-thumb {
                width: 52px;
                height: 52px;
            }

            .cell-clip {
                max-width: 220px;
            }
        }
    </style>
@endpush
