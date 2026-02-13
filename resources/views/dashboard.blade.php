@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<!-- page-specific styles could go here -->
@endpush

@section('content')
  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon bg-gradient-primary text-white me-2">
        <i class="mdi mdi-home"></i>
      </span> Dashboard
    </h3>
    <nav aria-label="breadcrumb">
      <ul class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">
          <span></span>Overview <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle"></i>
        </li>
      </ul>
    </nav>
  </div>

  <div class="row">
    <div class="col-md-4 stretch-card grid-margin">
      <div class="card bg-gradient-danger card-img-holder text-white">
        <div class="card-body">
          <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
          <h4 class="font-weight-normal mb-3">Total Kategori <i class="mdi mdi-book-open-variant mdi-24px float-end"></i>
          </h4>
          <h2 class="mb-5">{{ $kategoriCount ?? 0 }}</h2>
          <a href="{{ route('kategori.index') }}" class="text-white">Lihat semua kategori</a>
        </div>
      </div>
    </div>

    <div class="col-md-4 stretch-card grid-margin">
      <div class="card bg-gradient-info card-img-holder text-white">
        <div class="card-body">
          <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
          <h4 class="font-weight-normal mb-3">Total Buku <i class="mdi mdi-book-multiple mdi-24px float-end"></i>
          </h4>
          <h2 class="mb-5">{{ $bukuCount ?? 0 }}</h2>
          <a href="{{ route('buku.index') }}" class="text-white">Lihat semua buku</a>
        </div>
      </div>
    </div>

    <div class="col-md-4 stretch-card grid-margin">
      <div class="card bg-gradient-success card-img-holder text-white">
        <div class="card-body">
          <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
          <h4 class="font-weight-normal mb-3">Buku Terbaru <i class="mdi mdi-star mdi-24px float-end"></i>
          </h4>
          <h2 class="mb-5">{{ ($recentBukus->count() ?? 0) }}</h2>
          <div>Bulan ini</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12 grid-margin">
      <div class="row">
        <div class="col-lg-7 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Daftar Kategori</h4>
              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th>No</th>
                      <th>Nama Kategori</th>
                      <th>Jumlah Buku</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($recentKategoris as $i => $rk)
                      <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $rk->nama }}</td>
                        <td>
                          <label class="badge badge-gradient-primary">{{ $rk->bukus()->count() }}</label>
                        </td>
                        <td>
                          <a href="{{ route('kategori.edit',$rk) }}" class="btn btn-warning btn-sm">Edit</a>
                        </td>
                      </tr>
                    @empty
                      <tr><td colspan="4" class="text-center text-muted">Tidak ada kategori</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-5 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Buku Terbaru</h4>
              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Kode</th>
                      <th>Judul</th>
                      <th>Kategori</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($recentBukus as $rb)
                      <tr>
                        <td>{{ 'BK-'.str_pad($rb->id,2,'0',STR_PAD_LEFT) }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($rb->judul,24) }}</td>
                        <td><label class="badge badge-gradient-success">{{ optional($rb->kategori)->nama }}</label></td>
                      </tr>
                    @empty
                      <tr><td colspan="3" class="text-center text-muted">Tidak ada buku</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


@endsection

@push('scripts')
<script>
  (function(){
    const initialCategories = ['Novel','Biografi','Komik'];
    const kategoriList = document.getElementById('kategoriList');
    const kategoriForm = document.getElementById('kategoriForm');
    const kategoriInput = document.getElementById('kategoriInput');
    const bukuForm = document.getElementById('bukuForm');
    const bukuList = document.getElementById('bukuList');
    const bukuTitle = document.getElementById('bukuTitle');
    const bukuKategori = document.getElementById('bukuKategori');
    const clearBooks = document.getElementById('clearBooks');

    let categories = JSON.parse(localStorage.getItem('categories') || 'null') || initialCategories.slice();
    let books = JSON.parse(localStorage.getItem('books') || 'null') || [];

    function renderCategories(){
      kategoriList.innerHTML = '';
      bukuKategori.innerHTML = '';
      categories.forEach(cat => {
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.textContent = cat;
        kategoriList.appendChild(li);

        const opt = document.createElement('option');
        opt.value = cat;
        opt.textContent = cat;
        bukuKategori.appendChild(opt);
      });
      localStorage.setItem('categories', JSON.stringify(categories));
    }

    function renderBooks(){
      bukuList.innerHTML = '';
      books.forEach((b, idx) => {
        const li = document.createElement('li');
        li.className = 'list-group-item';
        li.innerHTML = `<strong>${escapeHtml(b.title)}</strong> <small class="text-muted">- ${escapeHtml(b.category)}</small>`;
        bukuList.appendChild(li);
      });
      localStorage.setItem('books', JSON.stringify(books));
    }

    function escapeHtml(str){
      return (str+'').replace(/[&<>"'`]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','`':'&#96;'})[s]);
    }

    kategoriForm.addEventListener('submit', function(e){
      e.preventDefault();
      const v = (kategoriInput.value || '').trim();
      if(!v) return;
      if(!categories.includes(v)) categories.push(v);
      kategoriInput.value = '';
      renderCategories();
    });

    bukuForm.addEventListener('submit', function(e){
      e.preventDefault();
      const title = (bukuTitle.value || '').trim();
      const category = bukuKategori.value || '';
      if(!title || !category) return;
      books.push({ title, category });
      bukuTitle.value = '';
      renderBooks();
    });

    clearBooks.addEventListener('click', function(){
      if(!confirm('Hapus semua buku?')) return;
      books = [];
      renderBooks();
    });

    // initial render
    renderCategories();
    renderBooks();
  })();
</script>
@endpush
