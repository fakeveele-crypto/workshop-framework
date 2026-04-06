@extends('layouts.app')

@section('title', 'Kantin Customer')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Kantin Customer</h3>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <label for="guestId" class="form-label fw-bold">Guest ID</label>
                            <input type="text" id="guestId" class="form-control" value="{{ $guestId }}" readonly>
                            <small class="text-muted">Nomor ini dihasilkan otomatis dari database.</small>
                        </div>
                        <div class="col-md-8">
                            <div class="alert alert-info mb-0">
                                Customer dapat memilih vendor, menambahkan menu ke keranjang, lalu lanjut ke halaman pembayaran Xendit Invoice.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0">Area Pemilihan POS</h4>
                </div>
                <div class="card-body">
                    <div id="customerAlert" class="alert d-none" role="alert"></div>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="vendorSelect" class="form-label">Pilih Vendor</label>
                            <select id="vendorSelect" class="form-select">
                                <option value="">-- Pilih Vendor --</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->idvendor }}">{{ $vendor->nama_vendor }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="menuSelect" class="form-label">Pilih Menu</label>
                            <select id="menuSelect" class="form-select" disabled>
                                <option value="">-- Pilih Vendor Dahulu --</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="jumlahInput" class="form-label">Jumlah</label>
                            <input type="number" id="jumlahInput" class="form-control" min="1" value="1">
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="button" id="addToCartButton" class="btn btn-success" disabled>Tambahkan</button>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="small text-muted">Menu akan otomatis difilter berdasarkan vendor yang dipilih.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h4 class="mb-0">Keranjang Belanja</h4>
                    <span class="badge bg-primary" id="cartCounter">0 item</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Menu</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-center" style="width: 120px;">Jumlah</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-center" style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="cartTableBody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada menu dipilih.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-primary">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="text-muted">Total Harga</div>
                        <h3 class="mb-0" id="grandTotal">Rp 0</h3>
                    </div>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <span class="badge bg-info text-dark">Pembayaran Xendit Invoice: Redirect</span>
                        <button type="button" id="payButton" class="btn btn-primary btn-lg" disabled>Bayar Sekarang</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="checkoutUrl" value="{{ route('customer.checkout') }}">
    <input type="hidden" id="menusUrlTemplate" value="{{ route('customer.menus', ['idvendor' => '__VENDOR__']) }}">
    <input type="hidden" id="guestIdValue" value="{{ $guestId }}">
@endsection

@push('scripts')
    <script>
        (function () {
            const guestIdInput = document.getElementById('guestId');
            const vendorSelect = document.getElementById('vendorSelect');
            const menuSelect = document.getElementById('menuSelect');
            const jumlahInput = document.getElementById('jumlahInput');
            const addToCartButton = document.getElementById('addToCartButton');
            const cartTableBody = document.getElementById('cartTableBody');
            const grandTotalEl = document.getElementById('grandTotal');
            const payButton = document.getElementById('payButton');
            const alertBox = document.getElementById('customerAlert');
            const checkoutUrl = document.getElementById('checkoutUrl')?.value || '';
            const menusUrlTemplate = document.getElementById('menusUrlTemplate')?.value || '';
            const guestId = document.getElementById('guestIdValue')?.value || '';
            const cartCounter = document.getElementById('cartCounter');

            if (!vendorSelect || !menuSelect || !jumlahInput || !addToCartButton || !cartTableBody || !payButton) {
                return;
            }

            const currencyFormatter = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            });

            const cart = new Map();
            let selectedMenu = null;

            const showAlert = function (type, message) {
                if (!alertBox) {
                    return;
                }

                alertBox.className = 'alert alert-' + type;
                alertBox.textContent = message;
                alertBox.classList.remove('d-none');
            };

            const clearAlert = function () {
                if (!alertBox) {
                    return;
                }

                alertBox.className = 'alert d-none';
                alertBox.textContent = '';
            };

            const formatCurrency = function (value) {
                return currencyFormatter.format(Number(value) || 0);
            };

            const parseQty = function (value) {
                const parsed = Number.parseInt(value, 10);
                return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
            };

            const getTotal = function () {
                let total = 0;

                cart.forEach(function (item) {
                    total += Number(item.subtotal) || 0;
                });

                return total;
            };

            const setLoading = function (button, isLoading, label) {
                if (!button.dataset.originalText) {
                    button.dataset.originalText = button.textContent;
                }

                if (isLoading) {
                    button.disabled = true;
                    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + label;
                    return;
                }

                button.innerHTML = button.dataset.originalText;
                syncButtons();
            };

            const syncButtons = function () {
                const hasVendor = Boolean(vendorSelect.value);
                const hasMenu = Boolean(menuSelect.value);
                const qty = parseQty(jumlahInput.value);

                if (!hasVendor) {
                    menuSelect.disabled = true;
                }

                addToCartButton.disabled = !hasVendor || !hasMenu || qty <= 0 || !selectedMenu;
                payButton.disabled = cart.size === 0;
                grandTotalEl.textContent = formatCurrency(getTotal());

                if (cartCounter) {
                    cartCounter.textContent = cart.size + ' item';
                }
            };

            const resetMenuSelection = function () {
                selectedMenu = null;
                menuSelect.innerHTML = '<option value="">-- Pilih Menu --</option>';
                menuSelect.disabled = true;
                menuSelect.value = '';
                jumlahInput.value = '1';
                syncButtons();
            };

            const loadMenus = async function (idvendor) {
                resetMenuSelection();

                if (!idvendor) {
                    return;
                }

                const url = menusUrlTemplate.replace('__VENDOR__', encodeURIComponent(idvendor));
                menuSelect.innerHTML = '<option value="">Memuat menu...</option>';

                try {
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Gagal memuat menu vendor.');
                    }

                    const menus = Array.isArray(data.data) ? data.data : [];

                    if (menus.length === 0) {
                        menuSelect.innerHTML = '<option value="">Tidak ada menu pada vendor ini</option>';
                        menuSelect.disabled = true;
                        return;
                    }

                    menuSelect.innerHTML = '<option value="">-- Pilih Menu --</option>';

                    menus.forEach(function (menu) {
                        const option = document.createElement('option');
                        option.value = String(menu.idmenu);
                        option.textContent = menu.nama_menu + ' - ' + formatCurrency(menu.harga);
                        option.dataset.namaMenu = menu.nama_menu;
                        option.dataset.harga = String(menu.harga);
                        menuSelect.appendChild(option);
                    });

                    menuSelect.disabled = false;
                    clearAlert();
                } catch (error) {
                    menuSelect.innerHTML = '<option value="">-- Pilih Vendor Dahulu --</option>';
                    menuSelect.disabled = true;
                    showAlert('danger', error.message || 'Gagal memuat menu vendor.');
                }

                syncButtons();
            };

            const renderCart = function () {
                cartTableBody.innerHTML = '';

                if (cart.size === 0) {
                    cartTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada menu dipilih.</td></tr>';
                    syncButtons();
                    return;
                }

                cart.forEach(function (item) {
                    const row = document.createElement('tr');
                    row.dataset.idmenu = String(item.idmenu);

                    row.innerHTML = '' +
                        '<td>' + item.nama_menu + '</td>' +
                        '<td class="text-end">' + formatCurrency(item.harga) + '</td>' +
                        '<td class="text-center">' + item.jumlah + '</td>' +
                        '<td class="text-end">' + formatCurrency(item.subtotal) + '</td>' +
                        '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger js-remove-item">Hapus</button></td>';

                    cartTableBody.appendChild(row);
                });

                syncButtons();
            };

            const addSelectedMenuToCart = function () {
                if (!selectedMenu) {
                    return;
                }

                const jumlah = parseQty(jumlahInput.value);
                if (jumlah <= 0) {
                    showAlert('warning', 'Jumlah harus lebih dari 0.');
                    return;
                }

                const existing = cart.get(selectedMenu.idmenu);
                if (existing) {
                    existing.jumlah += jumlah;
                    existing.subtotal = existing.harga * existing.jumlah;
                    cart.set(existing.idmenu, existing);
                } else {
                    cart.set(selectedMenu.idmenu, {
                        idmenu: selectedMenu.idmenu,
                        nama_menu: selectedMenu.nama_menu,
                        harga: selectedMenu.harga,
                        jumlah: jumlah,
                        subtotal: selectedMenu.harga * jumlah,
                    });
                }

                selectedMenu = null;
                menuSelect.value = '';
                jumlahInput.value = '1';
                addToCartButton.disabled = true;
                clearAlert();
                renderCart();
            };

            const buildCheckoutPayload = function () {
                return {
                    guest_id: guestId,
                    idvendor: vendorSelect.value,
                    items: Array.from(cart.values()).map(function (item) {
                        return {
                            idmenu: item.idmenu,
                            nama_menu: item.nama_menu,
                            harga: item.harga,
                            jumlah: item.jumlah,
                            subtotal: item.subtotal,
                        };
                    }),
                    total: getTotal(),
                };
            };

            const startXenditCheckout = async function () {
                if (cart.size === 0) {
                    showAlert('warning', 'Keranjang masih kosong.');
                    return;
                }

                if (!vendorSelect.value) {
                    showAlert('warning', 'Silakan pilih vendor terlebih dahulu.');
                    return;
                }

                setLoading(payButton, true, 'Menyiapkan pembayaran...');

                try {
                    const response = await fetch(checkoutUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(buildCheckoutPayload()),
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Gagal membuat pesanan.');
                    }

                    const redirectUrl = data?.data?.invoice_url || data?.data?.snap_token;
                    if (!redirectUrl) {
                        throw new Error('URL pembayaran tidak diterima.');
                    }

                    showAlert('info', 'Pesanan dibuat. Anda akan diarahkan ke halaman pembayaran Xendit...');
                    window.location.href = redirectUrl;
                } catch (error) {
                    showAlert('danger', error.message || 'Gagal menyiapkan checkout.');
                } finally {
                    setLoading(payButton, false, 'Bayar Sekarang');
                    syncButtons();
                }
            };

            vendorSelect.addEventListener('change', function () {
                cart.clear();
                renderCart();
                clearAlert();
                loadMenus(vendorSelect.value);
            });

            menuSelect.addEventListener('change', function () {
                const option = menuSelect.selectedOptions[0];

                if (!option || !option.value) {
                    selectedMenu = null;
                    syncButtons();
                    return;
                }

                selectedMenu = {
                    idmenu: Number(option.value),
                    nama_menu: option.dataset.namaMenu || option.textContent,
                    harga: Number(option.dataset.harga) || 0,
                };

                clearAlert();
                syncButtons();
            });

            jumlahInput.addEventListener('input', syncButtons);

            addToCartButton.addEventListener('click', addSelectedMenuToCart);

            cartTableBody.addEventListener('click', function (event) {
                const removeButton = event.target.closest('.js-remove-item');

                if (!removeButton) {
                    return;
                }

                const row = removeButton.closest('tr');
                const idmenu = Number(row?.dataset?.idmenu || 0);

                if (!idmenu) {
                    return;
                }

                cart.delete(idmenu);
                renderCart();
            });

            payButton.addEventListener('click', startXenditCheckout);

            if (guestIdInput && !guestIdInput.value) {
                guestIdInput.value = guestId;
            }

            renderCart();
            syncButtons();
        })();
    </script>
@endpush