@extends('layouts.app')

@section('title','Input Barang - HTML Table CRUD')

@push('styles')
  <style>
    .crud-row:hover {
      cursor: pointer;
    }
  </style>
@endpush

@section('content')
  <div class="page-header d-flex justify-content-between align-items-center">
    <h3 class="page-title">Input Barang (HTML Table CRUD)</h3>
    <div class="d-flex gap-2">
      <a href="{{ route('inputbarang.html') }}" class="btn btn-outline-secondary">Buka Versi Non-CRUD</a>
      <a href="{{ route('inputbarang.datatables.crud') }}" class="btn btn-outline-primary">Buka Versi Datatables</a>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <form action="{{ route('inputbarang.store') }}" method="POST">
        @csrf
        <input type="hidden" name="redirect_to" value="htmlCrud">

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
        <table class="table table-bordered mb-0" id="crudHtmlTable">
          <thead>
            <tr>
              <th>ID barang</th>
              <th>Nama</th>
              <th>Harga</th>
            </tr>
          </thead>
          <tbody>
            @forelse($items as $item)
              <tr
                class="crud-row"
                data-id-barang="{{ $item['id_barang'] }}"
                data-nama="{{ $item['nama'] }}"
                data-harga="{{ $item['harga'] }}"
              >
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

  <div class="modal fade" id="crudBarangModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body p-4">
          <form id="updateItemForm" action="{{ route('inputbarang.update') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="redirect_to" value="htmlCrud">

            <div class="row mb-3 align-items-center">
              <label for="modal_id_barang" class="col-md-3 col-form-label">ID barang :</label>
              <div class="col-md-9">
                <input id="modal_id_barang" name="id_barang" class="form-control" readonly required>
              </div>
            </div>

            <div class="row mb-3 align-items-center">
              <label for="modal_nama" class="col-md-3 col-form-label">Nama barang :</label>
              <div class="col-md-9">
                <input id="modal_nama" name="nama" class="form-control" required>
              </div>
            </div>

            <div class="row mb-4 align-items-center">
              <label for="modal_harga" class="col-md-3 col-form-label">Harga barang:</label>
              <div class="col-md-9">
                <input id="modal_harga" name="harga" class="form-control" required>
              </div>
            </div>

            <div class="d-flex justify-content-between">
              <button
                type="button"
                id="btnDeleteItem"
                class="btn btn-danger px-5"
              >
                Hapus
              </button>
              <button type="button" class="btn btn-success btn-submit px-5">Ubah</button>
            </div>
          </form>

          <form id="deleteItemForm" action="{{ route('inputbarang.destroy') }}" method="POST" class="d-none">
            @csrf
            @method('DELETE')
            <input type="hidden" name="id_barang" id="delete_modal_id_barang" required>
            <input type="hidden" name="redirect_to" value="htmlCrud">
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const modalElement = document.getElementById('crudBarangModal');
      if (!modalElement) {
        return;
      }

      let showModal = null;
      let hideModal = null;
      if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const crudModal = bootstrap.Modal.getOrCreateInstance(modalElement);
        showModal = function () {
          crudModal.show();
        };
        hideModal = function () {
          crudModal.hide();
        };
      } else if (typeof window.jQuery !== 'undefined' && typeof window.jQuery(modalElement).modal === 'function') {
        showModal = function () {
          window.jQuery(modalElement).modal('show');
        };
        hideModal = function () {
          window.jQuery(modalElement).modal('hide');
        };
      } else {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';

        showModal = function () {
          modalElement.style.display = 'block';
          modalElement.classList.add('show');
          modalElement.removeAttribute('aria-hidden');
          document.body.classList.add('modal-open');

          if (!backdrop.parentNode) {
            document.body.appendChild(backdrop);
          }
        };

        hideModal = function () {
          modalElement.style.display = 'none';
          modalElement.classList.remove('show');
          modalElement.setAttribute('aria-hidden', 'true');
          document.body.classList.remove('modal-open');

          if (backdrop.parentNode) {
            backdrop.parentNode.removeChild(backdrop);
          }
        };

        modalElement.addEventListener('click', function (event) {
          if (event.target === modalElement) {
            hideModal();
          }
        });

        document.addEventListener('keydown', function (event) {
          if (event.key === 'Escape') {
            hideModal();
          }
        });
      }

      if (!showModal) {
        return;
      }

      const idInput = document.getElementById('modal_id_barang');
      const namaInput = document.getElementById('modal_nama');
      const hargaInput = document.getElementById('modal_harga');
      const updateForm = document.getElementById('updateItemForm');
      const deleteForm = document.getElementById('deleteItemForm');
      const deleteButton = document.getElementById('btnDeleteItem');
      const deleteIdInput = document.getElementById('delete_modal_id_barang');
      const rows = document.querySelectorAll('#crudHtmlTable tbody tr[data-id-barang]');

      if (deleteButton && deleteForm && updateForm) {
        deleteButton.addEventListener('click', function () {
          if (!window.confirm('Yakin ingin menghapus data ini?')) {
            return;
          }

          deleteButton.disabled = true;
          deleteButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...';

          deleteForm.submit();
        });

        updateForm.addEventListener('submit', function () {
          deleteButton.disabled = true;
        });
      }

      rows.forEach(function (row) {
        row.addEventListener('click', function () {
          const idBarang = row.getAttribute('data-id-barang') || '';
          const nama = row.getAttribute('data-nama') || '';
          const harga = row.getAttribute('data-harga') || '';

          idInput.value = idBarang;
          namaInput.value = nama;
          hargaInput.value = harga;
          deleteIdInput.value = idBarang;

          showModal();
        });
      });
    });
  </script>
@endpush
