@extends('layouts.app')

@section('title','Input Barang - HTML Table')

@section('content')
  <div class="page-header d-flex justify-content-between align-items-center">
    <h3 class="page-title">Input Barang (HTML Table)</h3>
    <div class="d-flex gap-2">
      <a href="{{ route('inputbarang.html.crud') }}" class="btn btn-outline-success">Buka Versi CRUD</a>
      <a href="{{ route('inputbarang.datatables') }}" class="btn btn-outline-primary">Buka Versi Datatables</a>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <form id="inputBarangForm" action="{{ route('inputbarang.store') }}" method="POST">
        @csrf
        <input type="hidden" name="redirect_to" value="html">

        <div class="row mb-3 align-items-center">
          <label for="nama" class="col-md-3 col-form-label">Nama barang</label>
          <div class="col-md-9">
            <input id="nama" name="nama" class="form-control" value="{{ old('nama') }}" required>
          </div>
        </div>

        <div class="row mb-3 align-items-center">
          <label for="harga" class="col-md-3 col-form-label">Harga barang</label>
          <div class="col-md-9">
            <input id="harga" name="harga" class="form-control" value="{{ old('harga') }}" required>
          </div>
        </div>

        <div class="text-end">
          <button type="button" id="submitInputBarang" class="btn btn-success btn-submit px-4">Submit</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      @if($errors->any())
        <div class="alert alert-danger mb-3">
          <ul class="mb-0">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="table-responsive">
        <table class="table table-bordered mb-0">
          <thead>
            <tr>
              <th>ID barang</th>
              <th>Nama</th>
              <th>Harga</th>
            </tr>
          </thead>
          <tbody>
            @forelse($items as $item)
              <tr>
                <td>{{ $item['id_barang'] }}</td>
                <td>{{ $item['nama'] }}</td>
                <td>{{ $item['harga'] }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="text-center text-muted">Belum ada data.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('inputBarangForm');
      const namaInput = document.getElementById('nama');
      const hargaInput = document.getElementById('harga');
      const submitButton = document.getElementById('submitInputBarang');

      if (namaInput) {
        namaInput.focus();
      }

      if (form) {
        form.addEventListener('keydown', function (event) {
          if (event.key === 'Enter' && event.target.tagName !== 'TEXTAREA') {
            if (submitButton && !submitButton.disabled) {
              event.preventDefault();
              submitButton.click();
            }
          }
        });
      }

      if (hargaInput) {
        hargaInput.addEventListener('input', function () {
          this.value = this.value.replace(/[^0-9.,]/g, '');
        });
      }
    });
  </script>
@endpush
