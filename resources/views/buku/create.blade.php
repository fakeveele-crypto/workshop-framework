@extends('layouts.app')

@section('title','Tambah Buku')

@section('content')
  <div class="page-header">
    <h3 class="page-title">Tambah Buku</h3>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('buku.store') }}" method="POST">
        @csrf
        <div class="mb-3">
          <label class="form-label">Kode</label>
          <input name="kode" class="form-control" value="{{ old('kode') }}">
        </div>
        <div class="mb-3">
          <label class="form-label">Judul</label>
          <input name="judul" class="form-control" value="{{ old('judul') }}" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Pengarang</label>
          <input name="pengarang" class="form-control" value="{{ old('pengarang') }}">
        </div>
        <div class="mb-3">
          <label class="form-label">Kategori</label>
          <select name="kategori_id" class="form-select" required>
            @foreach($kategoris as $k)
              <option value="{{ $k->getKey() }}" {{ old('kategori_id') == $k->getKey() ? 'selected' : '' }}>{{ $k->nama }}</option>
            @endforeach
          </select>
        </div>
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('buku.index') }}" class="btn btn-secondary">Batal</a>
      </form>
    </div>
  </div>

@endsection
