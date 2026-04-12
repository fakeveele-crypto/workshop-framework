<nav class="sidebar sidebar-offcanvas" id="sidebar">
	<ul class="nav">
		<li class="nav-item">
			<a class="nav-link" href="{{ url('/') }}">
				<span class="menu-title">Dashboard</span>
				<i class="mdi mdi-home menu-icon"></i>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="{{ route('kategori.index') }}">
				<span class="menu-title">Kategori</span>
				<i class="mdi mdi-format-list-bulleted menu-icon"></i>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="{{ route('buku.index') }}">
				<span class="menu-title">Buku</span>
				<i class="mdi mdi-book-open-variant menu-icon"></i>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="{{ route('barang.index') }}">
				<span class="menu-title">Barang</span>
				<i class="mdi mdi-package-variant-closed menu-icon"></i>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="{{ route('pdf.index') }}">
				<span class="menu-title">Dokumen</span>
				<i class="mdi mdi-file menu-icon"></i>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="{{ route('inputbarang.index') }}">
				<span class="menu-title">Input</span>
				<i class="mdi mdi-playlist-plus menu-icon"></i>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="{{ route('modul5.index') }}">
				<span class="menu-title">M5</span>
				<i class="mdi mdi-map-marker-radius menu-icon"></i>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="{{ auth()->check() ? route('vendor.index') : route('customer.index') }}">
				<span class="menu-title">Kantin</span>
				<i class="mdi mdi-store menu-icon"></i>
			</a>
		</li>
		@auth
			<li class="nav-item">
				<a
					class="nav-link customer-toggle-link"
					data-bs-toggle="collapse"
					href="#customerMenu"
					aria-expanded="{{ request()->routeIs('customer_data.*') ? 'true' : 'false' }}"
					aria-controls="customerMenu"
				>
					<span class="menu-title">Customer</span>
					<i class="mdi mdi-chevron-down customer-toggle-arrow"></i>
					<i class="mdi mdi-account-multiple menu-icon"></i>
				</a>
				<div class="collapse {{ request()->routeIs('customer_data.*') ? 'show' : '' }}" id="customerMenu">
					<ul class="nav flex-column sub-menu">
						<li class="nav-item">
							<a class="nav-link {{ request()->routeIs('customer_data.index') ? 'active' : '' }}" href="{{ route('customer_data.index') }}">Data Customer</a>
						</li>
						<li class="nav-item">
							<a class="nav-link {{ request()->routeIs('customer_data.create_blob') ? 'active' : '' }}" href="{{ route('customer_data.create_blob') }}">Tambah Customer 1</a>
						</li>
						<li class="nav-item">
							<a class="nav-link {{ request()->routeIs('customer_data.create_path') ? 'active' : '' }}" href="{{ route('customer_data.create_path') }}">Tambah Customer 2</a>
						</li>
					</ul>
				</div>
			</li>
		@endauth
	</ul>
</nav>

<style>
	.customer-toggle-link {
		position: relative;
	}

	.customer-toggle-arrow {
		font-size: 0.95rem;
		margin-left: auto;
		margin-right: 2rem;
		transition: transform 0.2s ease;
	}

	.customer-toggle-link[aria-expanded="true"] .customer-toggle-arrow {
		transform: rotate(180deg);
	}
</style>

