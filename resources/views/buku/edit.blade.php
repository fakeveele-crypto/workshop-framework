@extends('layouts.app')

@section('title','Edit Buku')

@section('content')
  <div class="page-header">
    <h3 class="page-title">Edit Buku</h3>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('buku.update',$buku) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
          <label class="form-label">Kode</label>
          <input name="kode" class="form-control" value="{{ old('kode', $buku->kode) }}">
        </div>
        <div class="mb-3">
          <label class="form-label">Judul</label>
          <input name="judul" class="form-control" value="{{ old('judul',$buku->judul) }}" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Pengarang</label>
          <input name="pengarang" class="form-control" value="{{ old('pengarang', $buku->pengarang) }}">
        </div>
        <div class="mb-3">
          <label class="form-label">Kategori</label>
          <select name="kategori_id" class="form-select" required>
            @foreach($kategoris as $k)
              <option value="{{ $k->getKey() }}" {{ $k->getKey() == old('kategori_id', $buku->kategori_id) ? 'selected' : '' }}>{{ $k->nama }}</option>
            @endforeach
          </select>
        </div>
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('buku.index') }}" class="btn btn-secondary">Batal</a>
      </form>
    </div>
  </div>

@endsection
