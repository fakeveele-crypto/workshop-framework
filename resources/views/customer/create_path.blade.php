@extends('layouts.app')

@section('title', 'Tambah Customer 2')

@section('content')
    @include('customer._camera_form', [
        'mode' => 'path',
        'action' => route('customer_data.store_path'),
    ])
@endsection
