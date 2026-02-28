@extends('layouts.app')

@section('title','Tambah Barang')

@section('content')
  <div class="page-header">
    <h3 class="page-title">Tambah Barang</h3>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('barang.store') }}" method="POST">
        @csrf
        @if(in_array('nama', $columns ?? [], true))
          <div class="mb-3">
            <label class="form-label">Nama</label>
            <input name="nama" class="form-control" value="{{ old('nama') }}" required>
          </div>
        @endif

        @if(in_array('harga', $columns ?? [], true))
          <div class="mb-3">
            <label class="form-label">Harga</label>
            <input type="number" step="0.01" name="harga" class="form-control" value="{{ old('harga') }}" required>
          </div>
        @endif

        @if(in_array('timestamp', $columns ?? [], true))
          <div class="mb-3">
            <label class="form-label">Timestamp</label>
            <input type="datetime-local" name="timestamp" class="form-control" value="{{ old('timestamp') }}">
          </div>
        @endif

        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('barang.index') }}" class="btn btn-secondary">Batal</a>
      </form>
    </div>
  </div>
@endsection
