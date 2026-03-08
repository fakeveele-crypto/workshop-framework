@extends('layouts.app')

@section('title','Input Barang - Datatables')

@push('styles')
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
  <div class="page-header d-flex justify-content-between align-items-center">
    <h3 class="page-title">Input Barang (Datatables)</h3>
    <a href="{{ route('inputbarang.html') }}" class="btn btn-outline-primary">Buka Versi HTML Table</a>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <form action="{{ route('inputbarang.store') }}" method="POST">
        @csrf
        <input type="hidden" name="redirect_to" value="datatables">

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
          <button type="button" class="btn btn-success btn-submit px-4">Submit</button>
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
        <table class="table table-bordered mb-0" id="inputBarangTable">
          <thead>
            <tr>
              <th>ID barang</th>
              <th>Nama</th>
              <th>Harga</th>
            </tr>
          </thead>
          <tbody>
            @foreach($items as $item)
              <tr>
                <td>{{ $item['id_barang'] }}</td>
                <td>{{ $item['nama'] }}</td>
                <td>{{ $item['harga'] }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
  <script>
    $(function () {
      $('#inputBarangTable').DataTable({
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Semua']],
        pageLength: 10,
        language: {
          search: 'Cari:',
          lengthMenu: 'Tampilkan _MENU_ data',
          info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
          infoEmpty: 'Tidak ada data',
          zeroRecords: 'Data tidak ditemukan',
          paginate: {
            first: 'Awal',
            last: 'Akhir',
            next: 'Berikutnya',
            previous: 'Sebelumnya'
          }
        }
      });
    });
  </script>
@endpush
