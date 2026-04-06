@extends('layouts.app')

@section('title', 'Kantin Vendor - Manajemen Menu')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Kantin Vendor</h3>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('vendor.index') }}" class="btn btn-primary">Manajemen Master Menu</a>
                <a href="{{ route('vendor.orders') }}" class="btn btn-outline-success">Daftar Pesanan Lunas</a>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">{{ $editingMenu ? 'Edit Menu' : 'Form Input Master Menu' }}</h4>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                action="{{ $editingMenu ? route('vendor.update', $editingMenu->idmenu) : route('vendor.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf
                @if($editingMenu)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="idvendor" class="form-label">Pilih Stan (Vendor)</label>
                        <select name="idvendor" id="idvendor" class="form-control" required>
                            <option value="">-- Pilih Vendor --</option>
                            @foreach($vendors as $vendor)
                                <option
                                    value="{{ $vendor->idvendor }}"
                                    @selected(old('idvendor', $editingMenu->idvendor ?? '') == $vendor->idvendor)
                                >
                                    {{ $vendor->nama_vendor }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nama_menu" class="form-label">Nama Makanan/Minuman</label>
                        <input
                            type="text"
                            name="nama_menu"
                            id="nama_menu"
                            class="form-control"
                            value="{{ old('nama_menu', $editingMenu->nama_menu ?? '') }}"
                            placeholder="Contoh: Nasi Goreng Spesial"
                            required
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="harga" class="form-label">Harga per Porsi</label>
                        <input
                            type="number"
                            min="1"
                            name="harga"
                            id="harga"
                            class="form-control"
                            value="{{ old('harga', $editingMenu->harga ?? '') }}"
                            placeholder="Contoh: 25000"
                            required
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="foto" class="form-label">Upload Foto (Opsional)</label>
                        <input
                            type="file"
                            name="foto"
                            id="foto"
                            class="form-control"
                            accept="image/*"
                        >
                        @if($editingMenu && $editingMenu->path_gambar)
                            <small class="text-muted d-block mt-2">Foto saat ini:</small>
                            <img
                                src="{{ asset('storage/' . $editingMenu->path_gambar) }}"
                                alt="Foto menu"
                                class="img-thumbnail mt-1"
                                style="max-width: 160px;"
                            >
                        @endif
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-success">
                        {{ $editingMenu ? 'Perbarui Menu' : 'Simpan Menu' }}
                    </button>
                    @if($editingMenu)
                        <a href="{{ route('vendor.index') }}" class="btn btn-outline-secondary">Batal Edit</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Tabel Daftar Menu</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vendor</th>
                            <th>Nama Menu</th>
                            <th>Harga</th>
                            <th>Foto</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($menus as $index => $menu)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $menu->vendor->nama_vendor ?? '-' }}</td>
                                <td>{{ $menu->nama_menu }}</td>
                                <td>Rp {{ number_format($menu->harga, 0, ',', '.') }}</td>
                                <td>
                                    @if($menu->path_gambar)
                                        <img
                                            src="{{ asset('storage/' . $menu->path_gambar) }}"
                                            alt="{{ $menu->nama_menu }}"
                                            class="img-thumbnail"
                                            style="max-width: 120px;"
                                        >
                                    @else
                                        <span class="badge badge-light">Tanpa foto</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('vendor.edit', $menu->idmenu) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('vendor.destroy', $menu->idmenu) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Hapus menu ini?')"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada menu yang ditambahkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
