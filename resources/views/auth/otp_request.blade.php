@extends('layouts.auth')

@section('title','Minta OTP')

@section('content')
  <div class="card">
    <div class="card-body">
      <h4 class="card-title">Minta Kode OTP</h4>
      <p class="card-description">Kode OTP akan dikirim ke email Anda</p>

      @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
      @endif
      @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
      @endif

      <form method="POST" action="{{ route('otp.send') }}">
        @csrf
        <div class="form-group">
          <label>Email</label>
          <input name="email" type="email" value="{{ old('email') }}" class="form-control" required autofocus>
        </div>

        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-gradient-primary btn-lg">Kirim Kode OTP</button>
          <a href="{{ route('login') }}" class="btn btn-outline-secondary">Kembali ke Login</a>
        </div>
      </form>
    </div>
  </div>

@endsection
