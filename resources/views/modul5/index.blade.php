@extends('layouts.app')

@section('title', 'Modul 5')

@section('content')
	<div class="card">
		<div class="card-header">
			<h4 class="mb-0">Index Modul 5</h4>
		</div>
		<div class="card-body">
			<div class="d-flex flex-wrap gap-2">
				<a href="{{ route('modul5.pilihanwilayah.ajax') }}" class="btn btn-primary">Pilihan Wilayah</a>
				<a href="{{ route('modul5.pos') }}" class="btn btn-success">POS</a>
			</div>
		</div>
	</div>
@endsection
