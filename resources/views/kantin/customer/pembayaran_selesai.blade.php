@extends('layouts.app')

@section('title', 'Pembayaran Selesai')

@push('styles')
<style>
    .payment-finish-wrap {
        min-height: calc(100vh - 220px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 0;
    }

    .payment-finish-card {
        max-width: 520px;
        width: 100%;
        border: 0;
        border-radius: 1.5rem;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.12);
        overflow: hidden;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .payment-finish-header {
        padding: 1.5rem 1.75rem 1rem;
        background: linear-gradient(135deg, #0d6efd 0%, #20c997 100%);
        color: #fff;
        text-align: center;
    }

    .payment-finish-body {
        padding: 1.75rem;
        text-align: center;
    }

    .payment-qrcode {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: #fff;
        border-radius: 1.25rem;
        box-shadow: inset 0 0 0 1px rgba(13, 110, 253, 0.08);
        margin: 1rem 0 1.25rem;
    }

    .payment-qrcode svg {
        display: block;
        width: 260px;
        height: 260px;
        max-width: 100%;
    }

    .payment-meta {
        color: #6c757d;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .payment-order-id {
        font-weight: 700;
        color: #0d6efd;
    }
</style>
@endpush

@section('content')
    <div class="payment-finish-wrap">
        <div class="card payment-finish-card">
            <div class="payment-finish-header">
                <h3 class="mb-1">Pembayaran Berhasil</h3>
                <p class="mb-0">Simpan QR Code ini sebagai bukti transaksi.</p>
            </div>
            <div class="payment-finish-body">
                <div class="alert {{ $paymentConfirmed ? 'alert-success' : 'alert-warning' }} mb-3">
                    {{ $paymentConfirmed ? 'Pembayaran sudah terkonfirmasi.' : 'Pembayaran sedang diproses. QR Code tetap ditampilkan sebagai bukti transaksi.' }}
                </div>
                <div class="payment-qrcode">
                    {!! $qrcode !!}
                </div>
                <div class="payment-meta mb-3">
                    QR Code ini berisi data pesanan dengan ID <span class="payment-order-id">{{ $order->idpesanan }}</span>.
                    Tunjukkan ke petugas jika diperlukan.
                </div>
                <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                    <a href="{{ route('customer.index') }}" class="btn btn-success btn-lg px-4">
                        Selesai / Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
