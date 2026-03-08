@extends('layouts.app')

@section('title', 'Input')

@section('content')
	<div class="page-header d-flex justify-content-between align-items-center">
		<h3 class="page-title">Input</h3>
	</div>

	<div class="card">
		<div class="card-header">
			<h4 class="mb-0">Menu Input</h4>
		</div>
		<div class="card-body">
			<div class="d-flex flex-wrap gap-2">
				<a href="{{ route('inputbarang.html') }}" class="btn btn-primary">Input Barang</a>
				<a href="{{ route('inputbarang.selectkota') }}" class="btn btn-info">Select Kota</a>
			</div>
		</div>
	</div>
@endsection
