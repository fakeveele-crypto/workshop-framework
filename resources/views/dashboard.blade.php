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
          @auth
            <a href="{{ route('kategori.index') }}" class="text-white">Lihat semua kategori</a>
          @else
            <a href="{{ route('login') }}" class="text-white">Login untuk melihat</a>
          @endauth
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
          @auth
            <a href="{{ route('buku.index') }}" class="text-white">Lihat semua buku</a>
          @else
            <a href="{{ route('login') }}" class="text-white">Login untuk melihat</a>
          @endauth
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
                    @auth
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
                    @else
                      <tr><td colspan="4" class="text-center">Silakan <a href="{{ route('login') }}">login</a> untuk melihat kategori.</td></tr>
                    @endauth
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
                    @auth
                      @forelse($recentBukus as $rb)
                      <tr>
                        <td>{{ 'BK-'.str_pad($rb->id,2,'0',STR_PAD_LEFT) }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($rb->judul,24) }}</td>
                        <td><label class="badge badge-gradient-success">{{ optional($rb->kategori)->nama }}</label></td>
                      </tr>
                      @empty
                        <tr><td colspan="3" class="text-center text-muted">Tidak ada buku</td></tr>
                      @endforelse
                    @else
                      <tr><td colspan="3" class="text-center">Silakan <a href="{{ route('login') }}">login</a> untuk melihat buku.</td></tr>
                    @endauth
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
