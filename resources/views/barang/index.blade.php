@extends('layouts.app')

@section('title','Daftar Barang')

@push('styles')
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
  <style>
    #barangTable_wrapper .dataTables_length label {
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }

    #barangTable_wrapper .dataTables_length select {
      min-width: 72px;
      padding-right: 28px;
    }

    #barangTable.dataTable thead .sorting:before,
    #barangTable.dataTable thead .sorting_asc:before,
    #barangTable.dataTable thead .sorting_desc:before {
      top: 35%;
    }

    #barangTable.dataTable thead .sorting:after,
    #barangTable.dataTable thead .sorting_asc:after,
    #barangTable.dataTable thead .sorting_desc:after {
      top: 58%;
    }
  </style>
@endpush

@section('content')
  <div class="page-header">
    <h3 class="page-title">Daftar Barang</h3>
  </div>

  <div class="card">
    <div class="card-body">
      <a href="{{ route('barang.create') }}" class="btn btn-primary mb-3">Tambah Barang</a>

      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif

      <div class="table-responsive">
        @php
          $displayColumns = collect($columns ?? [])->reject(function ($column) {
              return $column === 'timestamp' || trim((string) $column) === '';
          })->values();
        @endphp

        <table class="table" id="barangTable">
          <thead>
            <tr>
              @foreach($displayColumns as $column)
                <th>{{ ucfirst(str_replace('_', ' ', $column)) }}</th>
              @endforeach
              @if(in_array('idkategori', $displayColumns->all(), true))
                <th>Nama Kategori</th>
              @endif
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($barangs as $barang)
              <tr>
                @foreach($displayColumns as $column)
                  <td>{{ data_get($barang, $column) }}</td>
                @endforeach
                @if(in_array('idkategori', $displayColumns->all(), true))
                  <td>{{ optional($barang->kategori)->nama }}</td>
                @endif
                <td>
                  <a href="{{ route('barang.edit', $barang->getKey()) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                  <form action="{{ route('barang.destroy', $barang->getKey()) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus barang?')">Hapus</button>
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

@push('scripts')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
  <script>
    $(function () {
      $('#barangTable').DataTable({
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
