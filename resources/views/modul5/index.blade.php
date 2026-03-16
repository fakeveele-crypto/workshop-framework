@extends('layouts.app')

@section('title', 'Modul 5')

@section('content')
	<div class="card">
		<div class="card-header">
			<h4 class="mb-0">Index Modul 5</h4>
		</div>
		<div class="card-body">
			<a href="{{ route('modul5.pilihanwilayah.ajax') }}" class="btn btn-primary">Pilihan Wilayah</a>
		</div>
	</div>
@endsection
