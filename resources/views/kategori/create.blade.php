@extends('layouts.app')

@section('title','Tambah Kategori')

@section('content')
  <div class="page-header">
    <h3 class="page-title">Tambah Kategori</h3>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('kategori.store') }}" method="POST">
        @csrf
        <div class="mb-3">
          <label class="form-label">Nama Kategori</label>
          <input name="nama" class="form-control" required>
        </div>
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('kategori.index') }}" class="btn btn-secondary">Batal</a>
      </form>
    </div>
  </div>

@endsection
