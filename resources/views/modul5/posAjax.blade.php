@extends('layouts.app')

@section('title', 'Modul 5 - POS (Ajax)')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <h3 class="page-title">Modul 5 - POS (jQuery Ajax)</h3>
        <div class="d-flex gap-2">
            <a href="{{ route('modul5.index') }}" class="btn btn-outline-primary">Kembali ke Menu M5</a>
            <a href="{{ route('modul5.pos.axios') }}" class="btn btn-primary">Buka Versi Axios</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Halaman Kasir</h4>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-center mb-3">
                <label for="kodeBarang" class="col-md-2 col-form-label">Kode barang</label>
                <div class="col-md-10">
                    <input type="text" id="kodeBarang" class="form-control" autocomplete="off" placeholder="Masukkan kode lalu Enter">
                </div>
            </div>

            <div class="row g-3 align-items-center mb-3">
                <label for="namaBarang" class="col-md-2 col-form-label">Nama barang</label>
                <div class="col-md-10">
                    <input type="text" id="namaBarang" class="form-control" readonly>
                </div>
            </div>

            <div class="row g-3 align-items-center mb-3">
                <label for="hargaBarang" class="col-md-2 col-form-label">Harga barang</label>
                <div class="col-md-10">
                    <input type="text" id="hargaBarang" class="form-control" readonly>
                </div>
            </div>

            <div class="row g-3 align-items-center mb-3">
                <label for="jumlahBarang" class="col-md-2 col-form-label">Jumlah</label>
                <div class="col-md-10">
                    <input type="number" id="jumlahBarang" class="form-control" value="1" min="1">
                </div>
            </div>

            <div class="d-flex justify-content-end mb-4">
                <button type="button" id="btnTambahkan" class="btn btn-success" disabled>Tambahkan</button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="cartTable">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Harga</th>
                            <th style="width: 140px;">Jumlah</th>
                            <th>Subtotal</th>
                            <th style="width: 90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="cartEmptyRow">
                            <td colspan="6" class="text-center text-muted">Belum ada barang ditambahkan.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end align-items-center gap-3 mt-3">
                <h4 class="mb-0">Total: <span id="grandTotal">Rp 0</span></h4>
                <button type="button" id="btnBayar" class="btn btn-success" disabled>Bayar</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function () {
            const endpoints = {
                findBarang: '{{ route('modul5.pos.barang') }}',
                checkout: '{{ route('modul5.pos.checkout') }}',
            };

            const csrfToken = '{{ csrf_token() }}';

            const kodeInput = document.getElementById('kodeBarang');
            const namaInput = document.getElementById('namaBarang');
            const hargaInput = document.getElementById('hargaBarang');
            const jumlahInput = document.getElementById('jumlahBarang');
            const btnTambahkan = document.getElementById('btnTambahkan');
            const btnBayar = document.getElementById('btnBayar');
            const cartTableBody = document.querySelector('#cartTable tbody');
            const grandTotalEl = document.getElementById('grandTotal');

            if (!kodeInput || !namaInput || !hargaInput || !jumlahInput || !btnTambahkan || !btnBayar || !cartTableBody || !window.jQuery) {
                return;
            }

            const currencyFormatter = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            });

            const cart = new Map();
            let currentItem = null;

            const formatRupiah = function (value) {
                return currencyFormatter.format(Number(value) || 0);
            };

            const parsePositiveInt = function (value) {
                const parsed = Number.parseInt(value, 10);
                return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
            };

            const setButtonLoading = function (button, isLoading, loadingText) {
                if (!button.dataset.originalText) {
                    button.dataset.originalText = button.textContent;
                }

                if (isLoading) {
                    button.disabled = true;
                    button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' + loadingText;
                    return;
                }

                button.innerHTML = button.dataset.originalText;
            };

            const resetLookupForm = function () {
                currentItem = null;
                namaInput.value = '';
                hargaInput.value = '';
                jumlahInput.value = '1';
                syncTambahkanButton();
            };

            const getGrandTotal = function () {
                let total = 0;
                cart.forEach(function (item) {
                    total += Number(item.subtotal) || 0;
                });

                return total;
            };

            const syncBayarButton = function () {
                btnBayar.disabled = cart.size === 0;
            };

            const syncTambahkanButton = function () {
                const jumlah = parsePositiveInt(jumlahInput.value);
                btnTambahkan.disabled = !currentItem || jumlah <= 0;
            };

            const renderTable = function () {
                cartTableBody.innerHTML = '';

                if (cart.size === 0) {
                    cartTableBody.innerHTML = '<tr id="cartEmptyRow"><td colspan="6" class="text-center text-muted">Belum ada barang ditambahkan.</td></tr>';
                    grandTotalEl.textContent = formatRupiah(0);
                    syncBayarButton();
                    return;
                }

                cart.forEach(function (item) {
                    const tr = document.createElement('tr');
                    tr.setAttribute('data-kode', item.kode);

                    tr.innerHTML = '' +
                        '<td>' + item.kode + '</td>' +
                        '<td>' + item.nama + '</td>' +
                        '<td>' + formatRupiah(item.harga) + '</td>' +
                        '<td><input type="number" min="1" class="form-control form-control-sm js-row-jumlah" value="' + item.jumlah + '"></td>' +
                        '<td class="js-row-subtotal">' + formatRupiah(item.subtotal) + '</td>' +
                        '<td><button type="button" class="btn btn-sm btn-danger js-remove">Hapus</button></td>';

                    cartTableBody.appendChild(tr);
                });

                grandTotalEl.textContent = formatRupiah(getGrandTotal());
                syncBayarButton();
            };

            const mergeOrInsertItem = function () {
                if (!currentItem) {
                    return;
                }

                const jumlah = parsePositiveInt(jumlahInput.value);
                if (jumlah <= 0) {
                    return;
                }

                if (cart.has(currentItem.kode)) {
                    const existing = cart.get(currentItem.kode);
                    existing.jumlah += jumlah;
                    existing.subtotal = existing.jumlah * existing.harga;
                    cart.set(existing.kode, existing);
                } else {
                    cart.set(currentItem.kode, {
                        kode: currentItem.kode,
                        nama: currentItem.nama,
                        harga: currentItem.harga,
                        jumlah: jumlah,
                        subtotal: currentItem.harga * jumlah,
                    });
                }

                renderTable();
                kodeInput.value = '';
                resetLookupForm();
                kodeInput.focus();
            };

            const lookupBarang = function () {
                const kode = (kodeInput.value || '').trim();

                resetLookupForm();

                if (!kode) {
                    return;
                }

                setButtonLoading(btnTambahkan, true, 'Mencari...');

                window.jQuery.ajax({
                    url: endpoints.findBarang,
                    method: 'GET',
                    data: { kode: kode },
                    success: function (response) {
                        if (!response || !response.success || !response.data) {
                            return;
                        }

                        currentItem = {
                            kode: String(response.data.kode),
                            nama: String(response.data.nama),
                            harga: Number(response.data.harga) || 0,
                        };

                        namaInput.value = currentItem.nama;
                        hargaInput.value = formatRupiah(currentItem.harga);
                        jumlahInput.value = '1';
                        syncTambahkanButton();
                    },
                    error: function (xhr) {
                        const message = xhr?.responseJSON?.message || 'Kode barang tidak ditemukan.';
                        if (window.Swal) {
                            window.Swal.fire({
                                icon: 'warning',
                                title: 'Barang tidak ditemukan',
                                text: message,
                            });
                        }
                    },
                    complete: function () {
                        setButtonLoading(btnTambahkan, false, 'Tambahkan');
                        syncTambahkanButton();
                    },
                });
            };

            const checkout = function () {
                if (cart.size === 0) {
                    return;
                }

                const payloadItems = Array.from(cart.values()).map(function (item) {
                    return {
                        kode: item.kode,
                        nama: item.nama,
                        harga: item.harga,
                        jumlah: item.jumlah,
                        subtotal: item.subtotal,
                    };
                });

                const payload = {
                    items: payloadItems,
                    total: getGrandTotal(),
                };

                setButtonLoading(btnBayar, true, 'Menyimpan...');

                window.jQuery.ajax({
                    url: endpoints.checkout,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    data: payload,
                    success: function (response) {
                        if (window.Swal) {
                            window.Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response?.message || 'Pembayaran transaksi berhasil disimpan.',
                            });
                        }

                        cart.clear();
                        renderTable();
                        kodeInput.value = '';
                        resetLookupForm();
                        kodeInput.focus();
                    },
                    error: function (xhr) {
                        const message = xhr?.responseJSON?.message || 'Terjadi kesalahan saat menyimpan transaksi.';
                        if (window.Swal) {
                            window.Swal.fire({
                                icon: 'error',
                                title: 'Gagal menyimpan',
                                text: message,
                            });
                        }
                    },
                    complete: function () {
                        setButtonLoading(btnBayar, false, 'Bayar');
                        syncBayarButton();
                    },
                });
            };

            kodeInput.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();
                lookupBarang();
            });

            jumlahInput.addEventListener('input', function () {
                syncTambahkanButton();
            });

            btnTambahkan.addEventListener('click', function () {
                if (btnTambahkan.disabled) {
                    return;
                }

                mergeOrInsertItem();
            });

            cartTableBody.addEventListener('change', function (event) {
                const jumlahField = event.target.closest('.js-row-jumlah');
                if (!jumlahField) {
                    return;
                }

                const tr = jumlahField.closest('tr');
                if (!tr) {
                    return;
                }

                const kode = tr.getAttribute('data-kode') || '';
                if (!kode || !cart.has(kode)) {
                    return;
                }

                const jumlahBaru = parsePositiveInt(jumlahField.value);
                if (jumlahBaru <= 0) {
                    jumlahField.value = String(cart.get(kode).jumlah);
                    return;
                }

                const item = cart.get(kode);
                item.jumlah = jumlahBaru;
                item.subtotal = item.harga * item.jumlah;
                cart.set(kode, item);

                renderTable();
            });

            cartTableBody.addEventListener('click', function (event) {
                const removeButton = event.target.closest('.js-remove');
                if (!removeButton) {
                    return;
                }

                const tr = removeButton.closest('tr');
                if (!tr) {
                    return;
                }

                const kode = tr.getAttribute('data-kode') || '';
                if (!kode) {
                    return;
                }

                cart.delete(kode);
                renderTable();
            });

            btnBayar.addEventListener('click', function () {
                if (btnBayar.disabled) {
                    return;
                }

                checkout();
            });

            renderTable();
            syncTambahkanButton();
        })();
    </script>
@endpush
