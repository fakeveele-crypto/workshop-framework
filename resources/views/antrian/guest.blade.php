@extends('layouts.app')

@section('title', 'Pendaftaran Antrian')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-account-plus"></i>
        </span>
        Pendaftaran Antrian
    </h3>
</div>

<div class="row">
    <div class="col-lg-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Form Pendaftaran Pasien</h4>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <p class="card-description mb-4">
                    Jumlah antrian aktif saat ini: <strong>{{ $waitingCount }}</strong>
                </p>

                <form method="POST" action="{{ route('antrian.guest.store') }}">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="nama">Nama Pasien</label>
                        <input id="nama" type="text" name="nama" value="{{ old('nama') }}" class="form-control" required>
                    </div>

                    <div class="form-group mb-4">
                        <label for="poli">Poli Tujuan</label>
                        <select id="poli" name="poli" class="form-control">
                            <option value="Umum" {{ old('poli') === 'Umum' ? 'selected' : '' }}>Umum</option>
                            <option value="Jantung" {{ old('poli') === 'Jantung' ? 'selected' : '' }}>Jantung</option>
                            <option value="Gigi" {{ old('poli') === 'Gigi' ? 'selected' : '' }}>Gigi</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-gradient-primary">Ambil Nomor Antrian</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body d-flex flex-column justify-content-between">
                <div>
                    <h5 class="mb-3">Akses Cepat</h5>
                    <p class="mb-0">Pantau nomor yang sedang berjalan pada layar papan antrian.</p>
                </div>
                <a href="{{ $boardUrl }}" class="btn btn-outline-primary mt-4">Lihat Papan Antrian</a>
            </div>
        </div>
    </div>
</div>
@endsection
