@extends('layouts.app')

@section('title', 'Tiket Antrian')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-ticket-confirmation"></i>
        </span>
        Tiket Antrian
    </h3>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="card-title mb-3">Pendaftaran Berhasil!</h4>

                <div class="display-2 fw-bold mb-3">{{ $queue['number'] }}</div>

                <div class="mb-4">
                    <p class="mb-1"><strong>Nama Pasien:</strong> {{ $queue['nama'] }}</p>
                    <p class="mb-1"><strong>Poli Tujuan:</strong> {{ $queue['poli'] }}</p>
                    <p class="mb-0"><strong>Waktu Daftar:</strong> {{ $queue['created_at'] }}</p>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <button type="button" class="btn btn-gradient-primary" onclick="window.print()">Cetak</button>
                    <a href="{{ route('antrian.guest') }}" class="btn btn-outline-secondary">Daftar Lagi</a>
                    <a href="{{ route('antrian.board') }}" class="btn btn-outline-primary">Lihat Papan Antrian</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
