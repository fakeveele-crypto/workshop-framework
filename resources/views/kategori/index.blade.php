@extends('layouts.app')

@section('title','Daftar Kategori')

@section('content')
  <div class="page-header">
    <h3 class="page-title">Daftar Kategori</h3>
  </div>

  <div class="card">
    <div class="card-body">
      <a href="{{ route('kategori.create') }}" class="btn btn-primary mb-3">Tambah Kategori</a>

      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      <ul class="list-group">
        @foreach($kategoris as $k)
          <li class="list-group-item d-flex justify-content-between align-items-center">
            {{ $k->nama }}
            <span>
              <a href="{{ route('kategori.edit',$k) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
              <form action="{{ route('kategori.destroy',$k) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kategori?')">Hapus</button>
              </form>
            </span>
          </li>
        @endforeach
      </ul>
    </div>
  </div>

@endsection
