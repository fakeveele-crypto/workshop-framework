@php
    $isBlobMode = $mode === 'blob';
    $pageTitle = $isBlobMode ? 'Tambah Customer 1' : 'Tambah Customer 2';
    $saveDescription = $isBlobMode
        ? 'Foto akan disimpan sebagai BYTEA di database.'
        : 'Foto akan disimpan sebagai file di storage dan path-nya dicatat di database.';
@endphp

<div class="page-header">
    <h3 class="page-title">{{ $pageTitle }}</h3>
</div>

<div class="card">
    <div class="card-body">
        <p class="text-muted mb-4">{{ $saveDescription }}</p>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $action }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label" for="nama">Nama</label>
                        <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="alamat">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="2" required>{{ old('alamat') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="provinsi">Provinsi</label>
                        <input type="text" class="form-control" id="provinsi" name="provinsi" value="{{ old('provinsi') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="kota">Kota</label>
                        <input type="text" class="form-control" id="kota" name="kota" value="{{ old('kota') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="kecamatan">Kecamatan</label>
                        <input type="text" class="form-control" id="kecamatan" name="kecamatan" value="{{ old('kecamatan') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="kodepos_kelurahan">Kodepos - Kelurahan</label>
                        <input type="text" class="form-control" id="kodepos_kelurahan" name="kodepos_kelurahan" value="{{ old('kodepos_kelurahan') }}" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label d-block">Foto</label>
                    <div class="customer-photo-box border rounded d-flex align-items-center justify-content-center overflow-hidden" id="mainSnapshotContainer">
                        <span id="mainSnapshotPlaceholder" class="text-muted">Foto</span>
                        <img id="mainSnapshotPreview" src="" alt="Snapshot customer" class="img-fluid d-none">
                    </div>

                    <input type="hidden" name="foto_snapshot" id="fotoSnapshotInput" value="{{ old('foto_snapshot') }}">

                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-primary" id="openCameraBtn">Ambil Foto</button>
                        <button type="submit" class="btn btn-success">Simpan Data</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="cameraModal" tabindex="-1" aria-labelledby="cameraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cameraModalLabel">Modal ambil Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="camera-box border rounded d-flex align-items-center justify-content-center overflow-hidden">
                            <video id="cameraVideo" autoplay playsinline muted></video>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="camera-box border rounded d-flex align-items-center justify-content-center overflow-hidden">
                            <span id="modalSnapshotPlaceholder" class="text-muted">Snapshot</span>
                            <img id="modalSnapshotPreview" src="" alt="Hasil snapshot" class="img-fluid d-none">
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    <select id="cameraSelect" class="form-select w-auto"></select>
                    <button type="button" class="btn btn-outline-primary" id="refreshCameraBtn">Pilihan kamera</button>
                    <button type="button" class="btn btn-primary" id="captureBtn">Ambil Foto</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="savePhotoBtn">Simpan Foto</button>
            </div>
        </div>
    </div>
</div>

<canvas id="cameraCanvas" class="d-none"></canvas>

@push('styles')
    <style>
        .customer-photo-box,
        .camera-box {
            background: #f8f9fa;
            min-height: 180px;
        }

        #cameraVideo,
        #modalSnapshotPreview,
        #mainSnapshotPreview {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .customer-photo-box {
            max-width: 280px;
            min-height: 220px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEl = document.getElementById('cameraModal');
            const openCameraBtn = document.getElementById('openCameraBtn');
            const refreshCameraBtn = document.getElementById('refreshCameraBtn');
            const captureBtn = document.getElementById('captureBtn');
            const savePhotoBtn = document.getElementById('savePhotoBtn');
            const cameraSelect = document.getElementById('cameraSelect');
            const video = document.getElementById('cameraVideo');
            const canvas = document.getElementById('cameraCanvas');
            const modalSnapshotPreview = document.getElementById('modalSnapshotPreview');
            const modalSnapshotPlaceholder = document.getElementById('modalSnapshotPlaceholder');
            const mainSnapshotPreview = document.getElementById('mainSnapshotPreview');
            const mainSnapshotPlaceholder = document.getElementById('mainSnapshotPlaceholder');
            const photoInput = document.getElementById('fotoSnapshotInput');

            if (!modalEl || !openCameraBtn || !video || !canvas) {
                return;
            }

            const cameraModal = new bootstrap.Modal(modalEl);
            let stream = null;
            let snapshotDataUrl = '';

            const stopCamera = function () {
                if (!stream) {
                    return;
                }

                stream.getTracks().forEach(function (track) {
                    track.stop();
                });
                stream = null;
            };

            const loadCameraOptions = async function () {
                try {
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    const cameras = devices.filter(function (device) {
                        return device.kind === 'videoinput';
                    });

                    cameraSelect.innerHTML = '';
                    cameras.forEach(function (camera, index) {
                        const option = document.createElement('option');
                        option.value = camera.deviceId;
                        option.textContent = camera.label || ('Kamera ' + (index + 1));
                        cameraSelect.appendChild(option);
                    });
                } catch (error) {
                    console.error(error);
                }
            };

            const startCamera = async function (deviceId = '') {
                stopCamera();

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    alert('Browser tidak mendukung akses kamera.');
                    return;
                }

                const constraints = {
                    video: deviceId ? { deviceId: { exact: deviceId } } : { facingMode: 'user' },
                    audio: false
                };

                try {
                    stream = await navigator.mediaDevices.getUserMedia(constraints);
                    video.srcObject = stream;
                    await video.play();
                } catch (error) {
                    console.error(error);
                    alert('Kamera tidak dapat diakses. Pastikan izin kamera di browser sudah diberikan.');
                }
            };

            const setMainPreview = function (dataUrl) {
                if (!dataUrl) {
                    return;
                }

                mainSnapshotPreview.src = dataUrl;
                mainSnapshotPreview.classList.remove('d-none');
                mainSnapshotPlaceholder.classList.add('d-none');
            };

            if (photoInput.value) {
                setMainPreview(photoInput.value);
            }

            openCameraBtn.addEventListener('click', function () {
                cameraModal.show();
            });

            refreshCameraBtn.addEventListener('click', async function () {
                await loadCameraOptions();
                await startCamera(cameraSelect.value || '');
            });

            cameraSelect.addEventListener('change', function () {
                startCamera(cameraSelect.value || '');
            });

            captureBtn.addEventListener('click', function () {
                if (!video.srcObject) {
                    alert('Kamera belum aktif.');
                    return;
                }

                const width = video.videoWidth || 640;
                const height = video.videoHeight || 480;

                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, width, height);

                snapshotDataUrl = canvas.toDataURL('image/jpeg', 0.92);
                modalSnapshotPreview.src = snapshotDataUrl;
                modalSnapshotPreview.classList.remove('d-none');
                modalSnapshotPlaceholder.classList.add('d-none');
            });

            savePhotoBtn.addEventListener('click', function () {
                if (!snapshotDataUrl) {
                    alert('Silakan klik Ambil Foto terlebih dahulu.');
                    return;
                }

                photoInput.value = snapshotDataUrl;
                setMainPreview(snapshotDataUrl);
                cameraModal.hide();
            });

            modalEl.addEventListener('shown.bs.modal', async function () {
                snapshotDataUrl = '';
                modalSnapshotPreview.src = '';
                modalSnapshotPreview.classList.add('d-none');
                modalSnapshotPlaceholder.classList.remove('d-none');

                await loadCameraOptions();
                await startCamera(cameraSelect.value || '');
            });

            modalEl.addEventListener('hidden.bs.modal', function () {
                stopCamera();
            });

            window.addEventListener('beforeunload', function () {
                stopCamera();
            });
        });
    </script>
@endpush
