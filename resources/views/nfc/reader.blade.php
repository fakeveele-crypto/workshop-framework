<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi NFC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; margin: 0; padding: 0; }
        .card { border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h4 { font-weight: 600; }
        #nfc-status { font-size: 1.3rem; font-weight: bold; word-break: break-word; }
        .btn { font-size: 1rem; padding: 10px !important; }
        pre { font-size: 0.8rem !important; max-height: 200px; overflow-y: auto; border-radius: 4px; }
        .info-box { background: #f0f0f0; padding: 12px; border-radius: 6px; margin-bottom: 12px; }
        .info-label { font-size: 0.85rem; color: #666; margin-bottom: 4px; font-weight: 600; }
        .info-value { font-size: 1rem; color: #333; word-break: break-all; }
    </style>
</head>
<body>
    <div class="container-fluid p-3">
        <div class="card mt-2">
            <div class="card-body">
                <h4 class="mb-3">📱 Absensi NFC</h4>
                
                <!-- STATUS -->
                <div class="alert alert-info mb-3" role="alert">
                    <h3 id="nfc-status" class="m-0 text-center">Idle</h3>
                </div>

                <!-- TOMBOL -->
                <button id="btn-scan-nfc" class="btn btn-success w-100 mb-2">
                    ▶ Mulai Scan NFC
                </button>
                <button id="btn-stop-scan" class="btn btn-danger w-100 mb-3 d-none">
                    ⏹ Hentikan Scan
                </button>

                <!-- INFO HASIL -->
                <div class="info-box">
                    <div class="info-label">Serial Kartu</div>
                    <div class="info-value" id="nfc-serial">-</div>
                </div>

                <div class="info-box">
                    <div class="info-label">Timestamp</div>
                    <div class="info-value" id="nfc-timestamp">-</div>
                </div>

                <div class="info-box">
                    <div class="info-label">Data Record</div>
                    <pre id="nfc-records" class="m-0">-</pre>
                </div>

                <hr class="my-3">
                <p class="text-muted text-center small">
                    Gunakan Google Chrome di HP Android versi 89+<br>
                    Pastikan koneksi HTTPS atau ngrok
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const status = document.getElementById('nfc-status');
            const serial = document.getElementById('nfc-serial');
            const records = document.getElementById('nfc-records');
            const timestamp = document.getElementById('nfc-timestamp');
            const btnStart = document.getElementById('btn-scan-nfc');
            const btnStop = document.getElementById('btn-stop-scan');

            let ndefReader = null;
            let scanController = null;
            let isScanning = false;
            let timeoutId = null;

            function updateStatus(text) {
                status.innerText = text;
                console.log('STATUS:', text);
            }

            function setScanMode(active) {
                isScanning = active;
                btnStart.classList.toggle('d-none', active);
                btnStop.classList.toggle('d-none', !active);
            }

            function playBeep() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.value = 800;
                    gain.gain.setValueAtTime(0.3, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.15);
                } catch (e) {}
            }

            function clearTimeoutIfExists() {
                if (timeoutId) {
                    clearTimeout(timeoutId);
                    timeoutId = null;
                }
            }

            function stopScan() {
                if (scanController) {
                    try { scanController.abort(); } catch (e) {}
                    scanController = null;
                }
                ndefReader = null;
                clearTimeoutIfExists();
                setScanMode(false);
                updateStatus('Scan dihentikan');
            }

            btnStart.addEventListener('click', async function () {
                updateStatus('⏳ Memeriksa perangkat...');

                const ua = navigator.userAgent;
                const isAndroid = /Android/i.test(ua);
                const isChrome = /Chrome/i.test(ua) && !/CriOS|Edg/i.test(ua);
                const proto = window.location.protocol;
                const host = window.location.hostname;
                const valid = proto === 'https:' || host === 'localhost' || host === '127.0.0.1' || host.includes('ngrok');

                if (!isAndroid || !isChrome) {
                    updateStatus('❌ Harus menggunakan Chrome di HP Android');
                    return;
                }

                if (!valid) {
                    updateStatus('❌ Harus menggunakan HTTPS atau ngrok');
                    return;
                }

                if (!('NDEFReader' in window)) {
                    updateStatus('❌ Browser ini tidak mendukung Web NFC');
                    return;
                }

                setScanMode(true);
                updateStatus('⏳ Mengaktifkan NFC...');

                scanController = new AbortController();
                ndefReader = new NDEFReader();

                ndefReader.addEventListener('reading', (event) => {
                    console.log('✅ NFC Read!', event);
                    playBeep();
                    updateStatus('✅ SCAN BERHASIL!');

                    serial.innerText = event.serialNumber || '-';
                    timestamp.innerText = new Date().toLocaleString('id-ID');

                    let data = '-';
                    if (event.message && event.message.records && event.message.records.length > 0) {
                        const recs = [];
                        for (const rec of event.message.records) {
                            let payload = '';
                            try {
                                if (rec.data) {
                                    if (rec.data instanceof DataView) {
                                        payload = new TextDecoder().decode(rec.data);
                                    } else if (rec.data instanceof ArrayBuffer) {
                                        payload = new TextDecoder().decode(new DataView(rec.data));
                                    } else {
                                        payload = String(rec.data);
                                    }
                                }
                            } catch (e) {
                                payload = '[decode error]';
                            }
                            recs.push({ type: rec.recordType || 'unknown', data: payload || '-' });
                        }
                        data = JSON.stringify(recs, null, 2);
                    }
                    records.innerText = data;
                    clearTimeoutIfExists();
                    setTimeout(stopScan, 2000);
                });

                ndefReader.addEventListener('readingerror', (event) => {
                    console.error('❌ NFC Error', event);
                    updateStatus('❌ Kartu tidak terbaca! Coba lagi.');
                    stopScan();
                });

                try {
                    updateStatus('🟡 Menunggu izin NFC...');
                    await ndefReader.scan({ signal: scanController.signal });
                    updateStatus('🟢 SCAN AKTIF! Dekatkan kartu sekarang...');

                    timeoutId = setTimeout(() => {
                        if (isScanning) {
                            updateStatus('⏱️ Timeout 30 detik. Coba lagi.');
                            stopScan();
                        }
                    }, 30000);
                } catch (error) {
                    console.error('❌ Error', error);
                    if (error.name === 'NotAllowedError') {
                        updateStatus('❌ Akses NFC ditolak. Izinkan di browser.');
                    } else if (error.name === 'NotSupportedError') {
                        updateStatus('❌ Web NFC tidak didukung di browser ini.');
                    } else if (error.name === 'AbortError') {
                        updateStatus('❌ Scan dibatalkan.');
                    } else {
                        updateStatus('❌ Error: ' + (error.message || error.name || 'Unknown'));
                    }
                    stopScan();
                }
            });

            btnStop.addEventListener('click', function () {
                stopScan();
            });

            updateStatus('Siap. Klik tombol!');
        });
    </script>
</body>
</html>
