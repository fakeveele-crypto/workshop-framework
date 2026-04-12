@extends('layouts.app')

@section('title', 'Tambah Customer 1')

@section('content')
    @include('customer._camera_form', [
        'mode' => 'blob',
        'action' => route('customer_data.store_blob'),
    ])
@endsection
