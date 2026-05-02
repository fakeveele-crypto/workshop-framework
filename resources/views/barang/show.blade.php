@extends('layouts.app')

@section('title','Detail Barang')

@section('content')
  <div class="page-header">
    <h3 class="page-title">Detail Barang</h3>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="row">
        @if(in_array('nama', $columns ?? [], true))
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Nama</label>
            <p class="form-control-plaintext">{{ $barang->nama }}</p>
          </div>
        @endif

        @if(in_array('harga', $columns ?? [], true))
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Harga</label>
            <p class="form-control-plaintext">Rp {{ number_format($barang->harga, 0, ',', '.') }}</p>
          </div>
        @endif

        @if(in_array('id_barang', $columns ?? [], true))
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">ID Barang</label>
            <p class="form-control-plaintext">{{ $barang->id_barang }}</p>
          </div>
        @endif

        @if(in_array('timestamp', $columns ?? [], true) && $barang->timestamp)
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Timestamp</label>
            <p class="form-control-plaintext">{{ $barang->timestamp }}</p>
          </div>
        @endif
      </div>

      <div class="mt-3">
        <a href="{{ route('barang.edit', $barang->id_barang) }}" class="btn btn-primary">Edit</a>
        <a href="{{ route('barang.index') }}" class="btn btn-secondary">Kembali</a>
      </div>
    </div>
  </div>
@endsection