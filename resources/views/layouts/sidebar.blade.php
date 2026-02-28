<nav class="sidebar sidebar-offcanvas" id="sidebar">
	<ul class="nav">
		<li class="nav-item">
			<a class="nav-link" href="{{ url('/') }}">
				<span class="menu-title">Dashboard</span>
				<i class="mdi mdi-home menu-icon"></i>
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
			<a class="nav-link" href="{{ route('kategori.index') }}">
				<span class="menu-title">Kategori</span>
				<i class="mdi mdi-format-list-bulleted menu-icon"></i>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="{{ route('pdf.index') }}">
				<span class="menu-title">PDF</span>
				<i class="mdi mdi-file-pdf menu-icon"></i>
			</a>
		</li>
	</ul>
</nav>

