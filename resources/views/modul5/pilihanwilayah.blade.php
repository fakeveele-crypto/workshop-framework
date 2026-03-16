@extends('layouts.app')

@section('title', 'Modul 5 - Pilihan Wilayah')

@section('content')
	<div class="page-header d-flex justify-content-between align-items-center">
		<h3 class="page-title">Modul 5 - Pilihan Wilayah</h3>
		<a href="{{ route('modul5.index') }}" class="btn btn-outline-primary">Kembali ke menu M5</a>
	</div>

	<div class="alert alert-info" role="alert">
		Sumber data wilayah: <a href="https://github.com/guzfirdaus/Wilayah-Administrasi-Indonesia" target="_blank" rel="noopener">Wilayah Administrasi Indonesia (GitHub)</a>
	</div>

	<div class="card">
		<div class="card-header">
			<h4 class="mb-0">Pilih Versi</h4>
		</div>
		<div class="card-body d-flex flex-wrap gap-2">
			<a href="{{ route('modul5.pilihanwilayah.ajax') }}" class="btn btn-info">Versi jQuery Ajax</a>
			<a href="{{ route('modul5.pilihanwilayah.axios') }}" class="btn btn-primary">Versi Axios</a>
		</div>
	</div>
@endsection
