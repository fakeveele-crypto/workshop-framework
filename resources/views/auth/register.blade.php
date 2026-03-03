@extends('layouts.auth')

@section('title','Register')

@section('content')
  <div class="card">
    <div class="card-body">
      <h4 class="card-title">Register</h4>
      <p class="card-description">Buat akun baru</p>

      @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('register.post') }}">
        @csrf
        <div class="form-group">
          <label>Nama</label>
          <input name="name" type="text" value="{{ old('name') }}" class="form-control" required autofocus>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input name="email" type="email" value="{{ old('email') }}" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input name="password" type="password" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Konfirmasi Password</label>
          <input name="password_confirmation" type="password" class="form-control" required>
        </div>

        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-gradient-primary btn-lg">Register</button>
          <a href="{{ url('auth/google') }}" class="btn btn-outline-danger">Register dengan Google</a>
          <a href="{{ route('login') }}" class="btn btn-outline-secondary">Sudah punya akun? Login</a>
        </div>
      </form>
    </div>
  </div>
@endsection
