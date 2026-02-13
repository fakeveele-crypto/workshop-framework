@extends('layouts.app')

@section('title','Daftar Buku')

@section('content')
  <div class="page-header">
    <h3 class="page-title">Daftar Buku</h3>
  </div>

  <div class="card">
    <div class="card-body">
      <a href="{{ route('buku.create') }}" class="btn btn-primary mb-3">Tambah Buku</a>

      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Judul</th>
              <th>Pengarang</th>
              <th>Kategori</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($bukus as $b)
              <tr>
                <td>{{ $b->kode }}</td>
                <td>{{ $b->judul }}</td>
                <td>{{ $b->pengarang }}</td>
                <td>{{ optional($b->kategori)->nama }}</td>
                <td>
                  <a href="{{ route('buku.edit',$b) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                  <form action="{{ route('buku.destroy',$b) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus buku?')">Hapus</button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

@endsection
