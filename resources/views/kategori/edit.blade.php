@extends('layouts.app')

@section('title','Edit Kategori')

@section('content')
  <div class="page-header">
    <h3 class="page-title">Edit Kategori</h3>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('kategori.update',$kategori) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
          <label class="form-label">Nama Kategori</label>
          <input name="nama" class="form-control" value="{{ old('nama',$kategori->nama) }}" required>
        </div>
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('kategori.index') }}" class="btn btn-secondary">Batal</a>
      </form>
    </div>
  </div>

@endsection
