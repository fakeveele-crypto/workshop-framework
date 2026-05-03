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
      <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
        <a href="{{ route('barang.create') }}" class="btn btn-primary">Tambah Barang</a>

        <form action="{{ route('barang.print-labels') }}" method="POST" id="printLabelForm" target="_blank" class="d-flex flex-wrap align-items-end gap-2">
          @csrf
          <div>
            <label for="x" class="form-label mb-1">X (Kolom 1-5)</label>
            <input type="number" min="1" max="5" name="x" id="x" class="form-control" value="{{ old('x', 1) }}" required style="width: 150px;">
          </div>
          <div>
            <label for="y" class="form-label mb-1">Y (Baris 1-8)</label>
            <input type="number" min="1" max="8" name="y" id="y" class="form-control" value="{{ old('y', 1) }}" required style="width: 150px;">
          </div>
          <button type="button" class="btn btn-success btn-submit">Print Label</button>
        </form>
      </div>

      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
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

      <div id="scanResult"></div>

      <div class="table-responsive">
        @php
          $displayColumns = collect($columns ?? [])->reject(function ($column) {
              return $column === 'timestamp' || trim((string) $column) === '';
          })->values();
        @endphp

        <table class="table" id="barangTable">
          <thead>
            <tr>
              <th>
                <input type="checkbox" id="selectAllBarang" title="Pilih semua">
              </th>
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
                <td>
                  <input type="checkbox" class="row-checkbox" name="selected_barang[]" value="{{ $barang->getKey() }}" form="printLabelForm">
                </td>
                @foreach($displayColumns as $column)
                  <td>{{ data_get($barang, $column) }}</td>
                @endforeach
                @if(in_array('idkategori', $displayColumns->all(), true))
                  <td>{{ optional($barang->kategori)->nama }}</td>
                @endif
                <td>
                  <a href="{{ route('barang.show', $barang->getKey()) }}" class="btn btn-sm btn-outline-info">Detail</a>
                  <a href="{{ route('barang.edit', $barang->getKey()) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                  <form action="{{ route('barang.destroy', $barang->getKey()) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-sm btn-outline-danger btn-submit" data-confirm="Hapus barang?">Hapus</button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Scan Modal -->
  <div class="modal fade" id="scanModal" tabindex="-1" aria-labelledby="scanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="scanModalLabel">Scan Barcode</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="reader" style="width: 100%; min-height: 420px; max-width: 640px; margin: 0 auto;"></div>
        </div>
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
      const table = $('#barangTable').DataTable({
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Semua']],
        pageLength: 10,
        columnDefs: [
          { targets: [0, -1], orderable: false, searchable: false }
        ],
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
        },
        initComplete: function() {
          $('#barangTable_filter label').append('<button type="button" class="btn btn-sm btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#scanModal" title="Scan Barcode"><i class="mdi mdi-barcode-scan"></i></button>');
        }
      });

      $('#selectAllBarang').on('change', function () {
        const checked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', checked);
      });

      $('#barangTable tbody').on('change', '.row-checkbox', function () {
        const total = $('.row-checkbox').length;
        const checked = $('.row-checkbox:checked').length;
        $('#selectAllBarang').prop('checked', total > 0 && total === checked);
      });

      table.on('draw', function () {
        const total = $('.row-checkbox').length;
        const checked = $('.row-checkbox:checked').length;
        $('#selectAllBarang').prop('checked', total > 0 && total === checked);
      });

      let scanner = null;
      let scanProcessed = false;
      let scannerInitializing = false;

      $('#scanModal').on('shown.bs.modal', function () {
        // Jika scanner sudah aktif, jangan inisialisasi lagi
        if (scanner !== null) {
          return;
        }
        
        // Jika sedang proses inisialisasi, tunggu
        if (scannerInitializing) {
          return;
        }

        scanProcessed = false;
        scannerInitializing = true;
        $('#reader').empty().html('<p class="text-info">Initializing camera...</p>');

        // Tunggu sedikit untuk memastikan library sudah siap
        setTimeout(function() {
          // Cek apakah library tersedia
          if (typeof window.Html5QrcodeScanner === 'undefined') {
            console.error('Html5QrcodeScanner tidak tersedia');
            $('#reader').html('<p class="text-danger">Error: Barcode scanner library tidak tersedia. Silakan refresh halaman.</p>');
            scannerInitializing = false;
            return;
          }

          try {
            scanner = new window.Html5QrcodeScanner(
              "reader",
              {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                rememberLastUsedCamera: true,
                supportedScanTypes: [window.Html5QrcodeScanType.SCAN_TYPE_CAMERA]
              },
              /* verbose= */ false
            );
            
            scanner.render(onScanSuccess, onScanFailure);
            scannerInitializing = false;
          } catch (error) {
            console.error('Error initializing scanner:', error);
            $('#reader').html('<p class="text-danger">Error: ' + error.message + '. Silakan periksa izin kamera.</p>');
            scanner = null;
            scannerInitializing = false;
          }
        }, 300);
      });

      $('#scanModal').on('hidden.bs.modal', function () {
        if (scanner !== null) {
          try {
            scanner.clear().then(() => {
              scanner = null;
              $('#reader').empty();
            }).catch(function(err) {
              console.warn('Error clearing scanner:', err);
              scanner = null;
              $('#reader').empty();
            });
          } catch (error) {
            console.error('Error stopping scanner:', error);
            scanner = null;
            $('#reader').empty();
          }
        } else {
          $('#reader').empty();
        }
        $('body').removeClass('modal-open');
        
        // Hapus semua modal backdrop yang tersisa
        setTimeout(function() {
          $('.modal-backdrop').remove();
        }, 100);
      });

      function onScanSuccess(decodedText, decodedResult) {
        if (scanProcessed) {
          return;
        }
        scanProcessed = true;

        console.log('Barcode detected:', decodedText);

        // Play beep
        try {
          const audio = new Audio('/sounds/beep.mp3');
          audio.play().catch(function(err) {
            console.warn('Audio play warning:', err);
          });
        } catch (error) {
          console.warn('Could not play beep:', error);
        }

        // Stop scanner
        if (scanner) {
          try {
            scanner.clear().catch(function(err) {
              console.warn('Error clearing scanner:', err);
            });
          } catch (error) {
            console.error('Error stopping scanner:', error);
          }
        }

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('scanModal'));
        if (modal) {
          modal.hide();
        } else {
          $('#scanModal').modal('hide');
        }

        // Reset scanner untuk scan berikutnya
        scanner = null;

        // Fetch data dengan delay sedikit untuk memastikan modal tutup
        setTimeout(function () {
          fetch(`/barang/cek-data/${decodedText}`)
            .then(response => response.json())
            .then(data => {
              if (data.error) {
                $('#scanResult').html('<div class="alert alert-danger alert-dismissible fade show" role="alert">Barang tidak ditemukan: ' + decodedText + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
              } else {
                $('#scanResult').html(`
                  <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Hasil Scan:</strong> ID: ${data.id_barang}, Nama: ${data.nama}, Harga: Rp ${data.harga.toLocaleString('id-ID')}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>
                `);
              }
              // Reset untuk scan berikutnya
              scanProcessed = false;
            })
            .catch(error => {
              console.error('Error fetching barang data:', error);
              $('#scanResult').html('<div class="alert alert-danger alert-dismissible fade show" role="alert">Terjadi kesalahan: ' + error.message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
              scanProcessed = false;
            });
        }, 500);
      }

      function onScanFailure(error) {
        console.warn(`Code scan error = ${error}`);
      }
    });
  </script>
@endpush
