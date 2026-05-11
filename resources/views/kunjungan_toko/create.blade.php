@extends('layouts.app')

@section('title', 'Tambah Toko')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="page-title mb-1">Tambah Toko</h3>
            <p class="text-muted mb-0">Isi data toko dan simpan titik lokasi awal menggunakan geolokasi.</p>
        </div>
        <a href="{{ route('kunjungan_toko.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div id="locationAlert" class="alert d-none" role="alert"></div>

            <form method="POST" action="{{ route('kunjungan_toko.store') }}" id="storeForm" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label for="barcode" class="form-label">Barcode</label>
                    <input type="text" id="barcode" name="barcode" class="form-control" value="{{ old('barcode', $nextBarcode) }}" readonly>
                    <div class="form-text">Barcode otomatis memakai format TKO-xxx.</div>
                </div>

                <div class="col-md-6">
                    <label for="nama_toko" class="form-label">Nama Toko</label>
                    <input type="text" id="nama_toko" name="nama_toko" class="form-control" value="{{ old('nama_toko') }}" required maxlength="50">
                </div>

                <div class="col-md-4">
                    <label for="latitude" class="form-label">Latitude</label>
                    <input type="number" step="any" id="latitude" name="latitude" class="form-control" value="{{ old('latitude') }}" required>
                </div>

                <div class="col-md-4">
                    <label for="longitude" class="form-label">Longitude</label>
                    <input type="number" step="any" id="longitude" name="longitude" class="form-control" value="{{ old('longitude') }}" required>
                </div>

                <div class="col-md-4">
                    <label for="accuracy" class="form-label">Accuracy (meter)</label>
                    <input type="number" step="any" id="accuracy" name="accuracy" class="form-control" value="{{ old('accuracy') }}" required>
                </div>

                <div class="col-12 d-flex flex-wrap gap-2 align-items-center">
                    <button type="button" id="useCurrentLocation" class="btn btn-outline-primary">Gunakan lokasi saat ini</button>
                    <button type="submit" class="btn btn-primary">Simpan Toko</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const locationAlert = document.getElementById('locationAlert');
        const useCurrentLocationButton = document.getElementById('useCurrentLocation');
        const latitudeInput = document.getElementById('latitude');
        const longitudeInput = document.getElementById('longitude');
        const accuracyInput = document.getElementById('accuracy');

        function showLocationAlert(type, message) {
            locationAlert.className = 'alert alert-' + type;
            locationAlert.textContent = message;
            locationAlert.classList.remove('d-none');
        }

        useCurrentLocationButton.addEventListener('click', async () => {
            if (typeof window.getAccuratePosition !== 'function') {
                showLocationAlert('danger', 'Browser tidak mendukung geolokasi.');
                return;
            }

            useCurrentLocationButton.disabled = true;
            useCurrentLocationButton.textContent = 'Mengambil lokasi...';

            try {
                const position = await window.getAccuratePosition(50, 20000);
                latitudeInput.value = position.coords.latitude;
                longitudeInput.value = position.coords.longitude;
                accuracyInput.value = position.coords.accuracy.toFixed(2);
                showLocationAlert('success', 'Lokasi berhasil diambil dari perangkat.');
            } catch (error) {
                console.error(error);
                showLocationAlert('danger', error.message || 'Gagal mengambil lokasi saat ini.');
            } finally {
                useCurrentLocationButton.disabled = false;
                useCurrentLocationButton.textContent = 'Gunakan lokasi saat ini';
            }
        });
    </script>
@endpush