@extends('layouts.auth')

@section('title','Verifikasi OTP')

@section('content')
  <div class="card">
    <div class="card-body">
      <h4 class="card-title">Verifikasi OTP</h4>
      <p class="card-description">Masukkan kode OTP dari email</p>

      @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
      @endif
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      <p class="mb-3">Masukkan kode OTP yang dikirim ke <strong>{{ $email ?? 'alamat Anda' }}</strong>.
        @if(isset($otp_code))
          (untuk pengujian: <strong>{{ $otp_code }}</strong>)
        @endif
      </p>

      <form method="POST" action="{{ route('otp.verify') }}">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <div class="form-group">
          <label>Kode OTP</label>
          <input name="otp" class="form-control" value="{{ old('otp') }}" required>
        </div>

        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-gradient-primary btn-lg">Verifikasi</button>
          <a href="{{ route('otp.request') }}" class="btn btn-outline-secondary">Kirim Ulang</a>
        </div>
      </form>
    </div>
  </div>

@endsection
