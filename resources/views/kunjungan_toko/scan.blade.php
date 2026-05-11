@extends('layouts.app')

@section('title', 'Scan Kunjungan Toko')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="page-title mb-1">Scan Kunjungan Toko</h3>
            <p class="text-muted mb-0">Scan barcode toko lalu validasi lokasi sales dengan geolokasi.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('kunjungan_toko.index') }}" class="btn btn-outline-secondary">Kembali</a>
            <a href="{{ route('kunjungan_toko.history') }}" class="btn btn-outline-primary">History Scan</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div id="scanAlert" class="alert d-none" role="alert"></div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Kamera Scanner</h5>
                            <button type="button" id="startScannerButton" class="btn btn-primary btn-sm">Mulai Scan</button>
                        </div>
                        <div id="videoContainer" class="d-none">
                            <div id="reader"></div>
                        </div>
                        <div class="text-muted small mt-3">
                            Arahkan kamera ke barcode toko. Setelah terbaca, sistem akan mengambil lokasi sales dan menghitung jarak.
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100">
                        <h5 class="mb-3">Hasil Scan</h5>
                        <div id="resultPlaceholder" class="text-muted">Belum ada hasil scan.</div>
                        <div id="resultContainer" class="d-none"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const endpoints = {
            processScan: '{{ route('kunjungan_toko.process_scan') }}',
        };

        const csrfToken = '{{ csrf_token() }}';
        const startScannerButton = document.getElementById('startScannerButton');
        const videoContainer = document.getElementById('videoContainer');
        const resultPlaceholder = document.getElementById('resultPlaceholder');
        const resultContainer = document.getElementById('resultContainer');
        const scanAlert = document.getElementById('scanAlert');

        let html5QrcodeScanner = null;
        let scannerRendered = false;
        let scanInProgress = false;

        function showAlert(type, message) {
            scanAlert.className = 'alert alert-' + type;
            scanAlert.textContent = message;
            scanAlert.classList.remove('d-none');
        }

        function hideAlert() {
            scanAlert.classList.add('d-none');
        }

        function formatNumber(value, digits = 2) {
            return Number(value || 0).toLocaleString('id-ID', {
                minimumFractionDigits: digits,
                maximumFractionDigits: digits,
            });
        }

        async function startScanning() {
            hideAlert();
            resultPlaceholder.classList.remove('d-none');
            resultContainer.classList.add('d-none');
            videoContainer.classList.remove('d-none');

            if (scanInProgress || scannerRendered) {
                return;
            }

            scanInProgress = true;

            try {
                html5QrcodeScanner = new Html5QrcodeScanner('reader', {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    supportedScanTypes: [window.Html5QrcodeScanType.SCAN_TYPE_CAMERA],
                    rememberLastUsedCamera: true,
                    showTorchButtonIfSupported: true,
                }, false);

                await html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                scannerRendered = true;
                showAlert('info', 'Scanner aktif. Arahkan kamera ke barcode toko.');
            } catch (error) {
                console.error(error);
                scanInProgress = false;
                html5QrcodeScanner = null;
                showAlert('danger', 'Gagal membuka kamera untuk scan barcode.');
            }
        }

        async function stopScanner() {
            if (!html5QrcodeScanner || !scannerRendered) {
                videoContainer.classList.add('d-none');
                document.getElementById('reader').innerHTML = '';
                return;
            }

            try {
                await html5QrcodeScanner.clear();
            } catch (error) {
                console.error(error);
            }

            html5QrcodeScanner = null;
            scannerRendered = false;
            scanInProgress = false;
            videoContainer.classList.add('d-none');
            document.getElementById('reader').innerHTML = '';
        }

        function onScanSuccess(decodedText) {
            if (!scanInProgress) {
                return;
            }

            scanInProgress = false;
            playBeep();
            stopScanner().then(() => processScan(decodedText));
        }

        function onScanFailure() {
            // Biarkan kamera tetap aktif sampai barcode terbaca.
        }

        async function processScan(barcodeToko) {
            if (typeof window.getAccuratePosition !== 'function') {
                showAlert('danger', 'Browser tidak mendukung geolokasi.');
                return;
            }

            showAlert('info', 'Mengambil lokasi sales dengan akurasi terbaik...');

            try {
                const position = await window.getAccuratePosition(50, 20000);
                const response = await fetch(endpoints.processScan, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        barcode_toko: barcodeToko,
                        lat_sales: position.coords.latitude,
                        lng_sales: position.coords.longitude,
                        acc_sales: position.coords.accuracy,
                    }),
                });

                const payload = await response.json();

                if (!response.ok || !payload.success) {
                    throw new Error(payload.message || 'Gagal memproses scan.');
                }

                renderResult(payload.data, payload.message);
                showAlert(payload.data.status === '1' ? 'success' : 'warning', payload.message);
            } catch (error) {
                console.error(error);
                showAlert('danger', error.message || 'Terjadi kesalahan saat memproses scan.');
            }
        }

        function renderResult(data, message) {
            const badgeClass = data.status === '1' ? 'bg-success' : 'bg-danger';
            const content = `
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <h5 class="mb-0">${message}</h5>
                            <span class="badge ${badgeClass}">${data.status_label}</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6"><strong>Barcode Toko:</strong><br>${data.barcode_toko}</div>
                            <div class="col-md-6"><strong>Nama Toko:</strong><br>${data.nama_toko}</div>
                            <div class="col-md-6"><strong>Jarak Aktual:</strong><br>${formatNumber(data.jarak_aktual)} meter</div>
                            <div class="col-md-6"><strong>Threshold Efektif:</strong><br>${formatNumber(data.threshold_efektif)} meter</div>
                            <div class="col-md-6"><strong>Latitude Sales:</strong><br>${formatNumber(data.lat_sales, 6)}</div>
                            <div class="col-md-6"><strong>Longitude Sales:</strong><br>${formatNumber(data.lng_sales, 6)}</div>
                            <div class="col-md-6"><strong>Accuracy Sales:</strong><br>${formatNumber(data.acc_sales)} meter</div>
                            <div class="col-md-6"><strong>Waktu Kunjungan:</strong><br>${data.waktu_kunjungan ?? '-'}</div>
                        </div>
                    </div>
                </div>
            `;

            resultPlaceholder.classList.add('d-none');
            resultContainer.innerHTML = content;
            resultContainer.classList.remove('d-none');
        }

        function playBeep() {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 800;
            oscillator.type = 'square';

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.1);
        }

        startScannerButton.addEventListener('click', startScanning);
    </script>
@endpush