@extends('layouts.app')

@section('title', 'Panel Admin Antrian')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-monitor-dashboard"></i>
        </span>
        Panel Kontrol Antrian
    </h3>
</div>

<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex flex-wrap gap-2 align-items-center">
                <button id="btn-call-next" type="button" class="btn btn-gradient-primary">Panggil Berikutnya</button>
                <button id="btn-reset" type="button" class="btn btn-outline-danger">Reset Antrian Hari Ini</button>
                <a href="{{ $boardUrl }}" target="_blank" class="btn btn-outline-primary">Buka Papan Antrian</a>
                <span id="flash-message" class="ms-2"></span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card"><div class="card-body"><h6 class="card-title">Menunggu</h6><h3 id="stat-waiting">{{ $summary['waiting'] }}</h3></div></div>
    </div>
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card"><div class="card-body"><h6 class="card-title">Dipanggil</h6><h3 id="stat-called">{{ $summary['called'] }}</h3></div></div>
    </div>
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card"><div class="card-body"><h6 class="card-title">Terlambat</h6><h3 id="stat-late">{{ $summary['late'] }}</h3></div></div>
    </div>
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card"><div class="card-body"><h6 class="card-title">Selesai</h6><h3 id="stat-total">{{ $summary['total'] }}</h3></div></div>
    </div>
</div>

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Daftar Antrian Hari Ini</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Pasien</th>
                                <th>Poli / Layanan</th>
                                <th>Jam Daftar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="list-waiting">
                            @forelse($waitingQueues as $q)
                                <tr data-token="{{ $q['token'] }}" data-number="{{ $q['number'] }}" data-nama="{{ $q['nama'] }}" data-poli="{{ $q['poli'] }}" data-created_at="{{ $q['created_at'] }}" data-status="waiting">
                                    <td>{{ $q['number'] }}</td>
                                    <td>{{ $q['nama'] }}</td>
                                    <td>{{ $q['poli'] }}</td>
                                    <td>{{ $q['created_at'] }}</td>
                                    <td><span class="badge badge-gradient-info">MENUNGGU</span></td>
                                    <td>
                                        <button type="button" class="btn btn-outline-primary btn-sm me-1" data-action="call" data-token="{{ $q['token'] }}" title="Panggil">
                                            <i class="mdi mdi-volume-high"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-warning btn-sm" data-action="late" data-token="{{ $q['token'] }}" title="Tandai Terlewat">
                                            <i class="mdi mdi-clock-alert"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-muted text-center">Tidak ada antrian menunggu.</td></tr>
                            @endforelse
                        </tbody>
                        <tbody id="list-called">
                            @forelse($calledQueues as $q)
                                <tr data-token="{{ $q['token'] }}" data-number="{{ $q['number'] }}" data-nama="{{ $q['nama'] }}" data-poli="{{ $q['poli'] }}" data-created_at="{{ $q['created_at'] }}" data-status="called">
                                    <td>{{ $q['number'] }}</td>
                                    <td>{{ $q['nama'] }}</td>
                                    <td>{{ $q['poli'] }}</td>
                                    <td>{{ $q['created_at'] }}</td>
                                    <td><span class="badge badge-gradient-success">DIPANGGIL</span></td>
                                    <td>
                                        <button type="button" class="btn btn-outline-primary btn-sm me-1" data-action="recall" data-token="{{ $q['token'] }}" title="Panggil Ulang">
                                            <i class="mdi mdi-volume-high"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm me-1" data-action="complete" data-token="{{ $q['token'] }}" title="Selesai">
                                            <i class="mdi mdi-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-warning btn-sm" data-action="late" data-token="{{ $q['token'] }}" title="Tandai Terlewat">
                                            <i class="mdi mdi-clock-alert"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-muted text-center">Tidak ada antrian dipanggil.</td></tr>
                            @endforelse
                        </tbody>
                        <tbody id="list-late">
                            @forelse($lateQueues as $q)
                                <tr data-token="{{ $q['token'] }}" data-number="{{ $q['number'] }}" data-nama="{{ $q['nama'] }}" data-poli="{{ $q['poli'] }}" data-created_at="{{ $q['created_at'] }}" data-status="late">
                                    <td>{{ $q['number'] }}</td>
                                    <td>{{ $q['nama'] }}</td>
                                    <td>{{ $q['poli'] }}</td>
                                    <td>{{ $q['created_at'] }}</td>
                                    <td><span class="badge badge-gradient-warning">TERLAMBAT</span></td>
                                    <td>
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-action="recall" data-token="{{ $q['token'] }}" title="Panggil Ulang">
                                            <i class="mdi mdi-refresh"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-muted text-center">Tidak ada antrian terlambat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const csrfToken = @json(csrf_token());
    const callNextUrl = @json(route('antrian.call'));
    const lateUrlTemplate = @json(route('antrian.late', ['token' => '__TOKEN__']));
    const recallUrlTemplate = @json(route('antrian.recall', ['token' => '__TOKEN__']));
    const resetUrl = @json(route('antrian.reset'));

    const source = new EventSource("{{ $streamUrl }}");

    const el = {
        waiting: document.getElementById('stat-waiting'),
        called: document.getElementById('stat-called'),
        late: document.getElementById('stat-late'),
        total: document.getElementById('stat-total'),
        listWaiting: document.getElementById('list-waiting'),
        listCalled: document.getElementById('list-called'),
        listLate: document.getElementById('list-late'),
        flash: document.getElementById('flash-message'),
        callNext: document.getElementById('btn-call-next'),
        reset: document.getElementById('btn-reset'),
    };

    const esc = (v) => {
        const d = document.createElement('div');
        d.textContent = String(v ?? '');
        return d.innerHTML;
    };

    const badgeClass = (status) => {
        if (status === 'waiting') return 'badge badge-gradient-info';
        if (status === 'called') return 'badge badge-gradient-success';
        return 'badge badge-gradient-warning';
    };

    const postJson = async (url) => {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || data.success === false) {
            throw new Error(data.message || 'Permintaan gagal diproses.');
        }
        return data;
    };

    const showMessage = (msg, isError = false) => {
        el.flash.className = isError ? 'text-danger' : 'text-success';
        el.flash.textContent = msg;
        window.setTimeout(() => {
            el.flash.textContent = '';
        }, 3000);
    };

    const rowWaiting = (q) => `
        <tr data-token="${esc(q.token)}" data-number="${esc(q.number)}" data-nama="${esc(q.nama)}" data-poli="${esc(q.poli)}" data-created_at="${esc(q.created_at)}" data-status="waiting">
            <td>${esc(q.number)}</td>
            <td>${esc(q.nama)}</td>
            <td>${esc(q.poli)}</td>
            <td>${esc(q.created_at)}</td>
            <td><span class="${badgeClass('waiting')}">MENUNGGU</span></td>
            <td>
                <button type="button" class="btn btn-outline-primary btn-sm me-1" data-action="call" data-token="${esc(q.token)}" title="Panggil">
                    <i class="mdi mdi-volume-high"></i>
                </button>
                <button type="button" class="btn btn-outline-warning btn-sm" data-action="late" data-token="${esc(q.token)}" title="Tandai Terlewat">
                    <i class="mdi mdi-clock-alert"></i>
                </button>
            </td>
        </tr>
    `;

    const rowCalled = (q) => `
        <tr data-token="${esc(q.token)}" data-number="${esc(q.number)}" data-nama="${esc(q.nama)}" data-poli="${esc(q.poli)}" data-created_at="${esc(q.created_at)}" data-status="called">
            <td>${esc(q.number)}</td>
            <td>${esc(q.nama)}</td>
            <td>${esc(q.poli)}</td>
            <td>${esc(q.created_at)}</td>
            <td><span class="${badgeClass('called')}">DIPANGGIL</span></td>
            <td>
                <button type="button" class="btn btn-outline-primary btn-sm me-1" data-action="recall" data-token="${esc(q.token)}" title="Panggil Ulang">
                    <i class="mdi mdi-volume-high"></i>
                </button>
                <button type="button" class="btn btn-outline-success btn-sm me-1" data-action="complete" data-token="${esc(q.token)}" title="Selesai">
                    <i class="mdi mdi-check"></i>
                </button>
                <button type="button" class="btn btn-outline-warning btn-sm" data-action="late" data-token="${esc(q.token)}" title="Tandai Terlewat">
                    <i class="mdi mdi-clock-alert"></i>
                </button>
            </td>
        </tr>
    `;

    const rowLate = (q) => `
        <tr data-token="${esc(q.token)}" data-number="${esc(q.number)}" data-nama="${esc(q.nama)}" data-poli="${esc(q.poli)}" data-created_at="${esc(q.created_at)}" data-status="late">
            <td>${esc(q.number)}</td>
            <td>${esc(q.nama)}</td>
            <td>${esc(q.poli)}</td>
            <td>${esc(q.created_at)}</td>
            <td><span class="${badgeClass('late')}">TERLAMBAT</span></td>
            <td>
                <button type="button" class="btn btn-outline-primary btn-sm" data-action="recall" data-token="${esc(q.token)}" title="Panggil Ulang">
                    <i class="mdi mdi-refresh"></i>
                </button>
            </td>
        </tr>
    `;

    const emptyRow = (label) => `
        <tr>
            <td colspan="6" class="text-muted text-center">Tidak ada antrian ${label}.</td>
        </tr>
    `;

    const renderList = (tbody, list, rowFactory, emptyLabel) => {
        tbody.innerHTML = list.length ? list.map(rowFactory).join('') : emptyRow(emptyLabel);
    };

    const renderData = (data) => {
        const summary = data.summary || {};
        const waiting = data.waiting || [];
        const called = data.called || [];
        const late = data.late || [];

        el.waiting.textContent = summary.waiting ?? 0;
        el.called.textContent = summary.called ?? 0;
        el.late.textContent = summary.late ?? 0;
        el.total.textContent = summary.total ?? 0;

        renderList(el.listWaiting, waiting, rowWaiting, 'menunggu');
        renderList(el.listCalled, called, rowCalled, 'dipanggil');
        renderList(el.listLate, late, rowLate, 'terlambat');
    };

    const delegateAction = async (event) => {
        const btn = event.target.closest('button[data-action]');
        if (!btn) return;

        const action = btn.dataset.action;
        const token = btn.dataset.token;
        const tr = btn.closest('tr');

        try {
            if (action === 'call') {
                await postJson(callNextUrl);
                showMessage('Antrian berhasil dipanggil.');
                // optimistic DOM move: waiting -> called
                if (tr) {
                    const html = rowCalled({ token: token, number: tr.dataset.number, nama: tr.dataset.nama, poli: tr.dataset.poli, created_at: tr.dataset.created_at });
                    tr.remove();
                    el.listCalled.insertAdjacentHTML('afterbegin', html);
                    el.called.textContent = (parseInt(el.called.textContent || '0') + 1);
                    el.waiting.textContent = Math.max((parseInt(el.waiting.textContent || '0') - 1), 0);
                }
            } else if (action === 'late') {
                await postJson(lateUrlTemplate.replace('__TOKEN__', encodeURIComponent(token)));
                showMessage('Antrian dipindah ke terlambat.');
                // immediate DOM move: remove from current list and append to late
                if (tr) {
                    const html = rowLate({ token: token, number: tr.dataset.number, nama: tr.dataset.nama, poli: tr.dataset.poli, created_at: tr.dataset.created_at });
                    tr.remove();
                    el.listLate.insertAdjacentHTML('afterbegin', html);
                    // update stats
                    el.late.textContent = (parseInt(el.late.textContent || '0') + 1);
                    el.waiting.textContent = Math.max((parseInt(el.waiting.textContent || '0') - 1),0);
                }
            } else if (action === 'recall') {
                await postJson(recallUrlTemplate.replace('__TOKEN__', encodeURIComponent(token)));
                showMessage('Antrian dipanggil ulang.');
            } else if (action === 'complete') {
                const completeUrl = @json(route('antrian.complete', ['token' => '__TOKEN__']));
                await postJson(completeUrl.replace('__TOKEN__', encodeURIComponent(token)));
                showMessage('Antrian ditandai selesai.');
                if (tr) {
                    tr.remove();
                    el.total.textContent = (parseInt(el.total.textContent || '0') + 1);
                    el.called.textContent = Math.max((parseInt(el.called.textContent || '0') - 1),0);
                }
            }
        } catch (err) {
            showMessage(err.message, true);
        }
    };

    el.listWaiting.addEventListener('click', delegateAction);
    el.listCalled.addEventListener('click', delegateAction);
    el.listLate.addEventListener('click', delegateAction);

    el.callNext.addEventListener('click', async () => {
        try {
            await postJson(callNextUrl);
            showMessage('Nomor berikutnya berhasil dipanggil.');
        } catch (err) {
            showMessage(err.message, true);
        }
    });

    el.reset.addEventListener('click', async () => {
        if (!window.confirm('Reset seluruh antrian hari ini?')) return;
        try {
            await postJson(resetUrl);
            showMessage('Antrian berhasil direset.');
        } catch (err) {
            showMessage(err.message, true);
        }
    });

    source.addEventListener('queue-update', function(e) {
        const data = JSON.parse(e.data || '{}');
        renderData(data);
    });

    source.onerror = function () {
        showMessage('Koneksi real-time terputus. Mencoba menyambung ulang...', true);
    };
})();
</script>
@endpush
