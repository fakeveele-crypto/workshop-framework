<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Papan Antrian</title>
    @include('layouts.styleglobal')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="container-fluid py-4">
    <div class="row g-3 align-items-stretch">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <p class="text-uppercase mb-2">Nomor Dipanggil</p>
                    <h1 id="papan-number" class="display-1 mb-3">{{ $lastCalled['number'] ?? '-' }}</h1>
                    <h3 id="papan-nama" class="mb-2">{{ $lastCalled['nama'] ?? '-' }}</h3>
                    <h5 id="papan-poli" class="mb-0">{{ $lastCalled['poli'] ?? '-' }}</h5>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="card-title">Daftar Menunggu Selanjutnya</h4>
                    <div id="papan-waiting-list" class="list-group list-group-flush">
                        @if(count($waitingQueues ?? []) > 0)
                            @foreach($waitingQueues as $q)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">{{ $q['number'] }}</span>
                                    <span>{{ $q['nama'] }}</span>
                                    <span class="text-muted">{{ $q['poli'] }}</span>
                                </div>
                            @endforeach
                        @else
                            <div class="list-group-item text-muted">Belum ada antrian menunggu.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<audio id="audio-dingdong" src="{{ asset('sounds/dingdong.mp3') }}" preload="auto"></audio>

@include('layouts.javascriptglobal')
<script>
(function () {
    const source = new EventSource("{{ $streamUrl }}");
    let lastVersion = null;

    const speechLang = @json($speechConfig['lang'] ?? 'id-ID');
    const speechRate = @json($speechConfig['rate'] ?? 0.85);

    const elNumber = document.getElementById('papan-number');
    const elNama = document.getElementById('papan-nama');
    const elPoli = document.getElementById('papan-poli');
    const elWaiting = document.getElementById('papan-waiting-list');
    const audio = document.getElementById('audio-dingdong');

    const esc = (v) => {
        const d = document.createElement('div');
        d.textContent = String(v ?? '-');
        return d.innerHTML;
    };

    const renderWaitingList = (waiting) => {
        if (!Array.isArray(waiting) || waiting.length === 0) {
            elWaiting.innerHTML = '<div class="list-group-item text-muted">Belum ada antrian menunggu.</div>';
            return;
        }

        elWaiting.innerHTML = waiting.map((q) => `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <span class="fw-bold">${esc(q.number)}</span>
                <span>${esc(q.nama)}</span>
                <span class="text-muted">${esc(q.poli)}</span>
            </div>
        `).join('');
    };

    const updateDisplay = (data) => {
        const lastCalled = data.last_called || null;
        elNumber.textContent = lastCalled && lastCalled.number ? lastCalled.number : '-';
        elNama.textContent = lastCalled && lastCalled.nama ? lastCalled.nama : '-';
        elPoli.textContent = lastCalled && lastCalled.poli ? lastCalled.poli : '-';
        renderWaitingList(data.waiting || []);
    };

    source.addEventListener('queue-update', function(e) {
        const data = JSON.parse(e.data || '{}');
        const hasLastCalled = !!data.last_called;

        updateDisplay(data);

        if (data.version && data.version !== lastVersion && hasLastCalled) {
            // play bell then speak the call (only number + poli as requested)
            audio.currentTime = 0;
            audio.play().catch(() => null);
            audio.onended = function () {
                if (!window.speechSynthesis) return;
                const num = data.last_called.number || '-';
                const poli = data.last_called.poli || '-';
                const text = `Nomor antrian ${num}, silakan menuju ke poli ${poli}`;
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = speechLang || 'id-ID';
                utterance.rate = parseFloat(speechRate) || 0.85;
                window.speechSynthesis.cancel();
                window.speechSynthesis.speak(utterance);
            };
            lastVersion = data.version;
        }
    });
})();
</script>
</body>
</html>
