@extends('layouts.app')

@section('title', 'Scan Barcode')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Scan QR Code Pesanan</h3>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div id="alertBox" class="alert d-none" role="alert"></div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="qrInput" class="form-label">QR Code / ID Pesanan</label>
                                <input type="text" id="qrInput" class="form-control" placeholder="Masukkan ID pesanan atau scan QR">
                            </div>
                            <button type="button" id="scanButton" class="btn btn-primary">Scan QR Code</button>
                            <button type="button" id="manualButton" class="btn btn-secondary ms-2">Input Manual</button>
                        </div>
                        <div class="col-md-6">
                            <div id="videoContainer" class="d-none">
                                <div id="reader"></div>
                            </div>
                        </div>
                    </div>

                    <div id="resultContainer" class="mt-4 d-none">
                        <h5>Hasil Scan</h5>
                        <div id="resultContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let html5QrcodeScanner = null;
        let scannerRendered = false;
        let scanInProgress = false;

        document.getElementById('scanButton').addEventListener('click', startScanning);
        document.getElementById('manualButton').addEventListener('click', processManual);

        async function startScanning() {
            document.getElementById('videoContainer').classList.remove('d-none');
            document.getElementById('resultContainer').classList.add('d-none');
            document.getElementById('alertBox').classList.add('d-none');

            if (scanInProgress) {
                return;
            }

            if (html5QrcodeScanner && scannerRendered) {
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
                showAlert('info', 'Scanner aktif. Arahkan kamera ke QR code 2D.');
            } catch (error) {
                console.error(error);
                html5QrcodeScanner = null;
                scanInProgress = false;
                showAlert('danger', 'Gagal membuka kamera untuk scan QR code.');
            }
        }

        async function stopScanner() {
            if (!html5QrcodeScanner || !scannerRendered) {
                document.getElementById('videoContainer').classList.add('d-none');
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
            document.getElementById('videoContainer').classList.add('d-none');
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
            // Ignore decode failures while the camera is active.
        }

        async function processManual() {
            const qrCode = document.getElementById('qrInput').value.trim();
            if (!qrCode) {
                showAlert('warning', 'Masukkan ID pesanan terlebih dahulu.');
                return;
            }
            await stopScanner();
            processScan(qrCode);
        }

        function processScan(qrCode) {
            fetch('{{ route("vendor.process_scan") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ qr_code: qrCode })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayResult(data.data);
                    showAlert('success', 'QR code berhasil dipindai.');
                } else {
                    showAlert('danger', data.message || 'Gagal memproses scan.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Terjadi kesalahan saat memproses scan.');
            });
        }

        function displayResult(data) {
            let content = `
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Detail Pesanan #${data.order_id}</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>ID Barcode:</strong> ${data.barcode_value ?? data.order_id}</p>
                        <p><strong>Status Bayar:</strong> <span class="badge ${data.status_bayar ? 'bg-success' : 'bg-danger'}">${data.status_bayar ? 'Sudah Bayar' : 'Belum Bayar'}</span></p>
                        <p><strong>Total:</strong> Rp ${data.total.toLocaleString('id-ID')}</p>
                        <h6>Menu yang Dipesan:</h6>
                        <ul class="list-group">
            `;

            data.menus.forEach(menu => {
                content += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        ${menu.nama_menu}
                        <span class="badge bg-primary rounded-pill">${menu.jumlah} x Rp ${menu.harga.toLocaleString('id-ID')}</span>
                    </li>
                `;
            });

            content += `
                        </ul>
                    </div>
                </div>
            `;

            document.getElementById('resultContent').innerHTML = content;
            document.getElementById('resultContainer').classList.remove('d-none');
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

        function showAlert(type, message) {
            const alertBox = document.getElementById('alertBox');
            alertBox.className = `alert alert-${type}`;
            alertBox.textContent = message;
            alertBox.classList.remove('d-none');
            setTimeout(() => alertBox.classList.add('d-none'), 5000);
        }
    </script>
@endpush