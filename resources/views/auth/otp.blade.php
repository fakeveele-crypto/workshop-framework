@extends('layouts.app')

@section('title','Verifikasi OTP')

@section('content')
  <div class="page-header">
    <h3 class="page-title">Verifikasi OTP</h3>
  </div>

  <div class="card">
    <div class="card-body" style="max-width:420px">
      @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
      @endif
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      <p>Masukkan kode OTP yang dikirim ke <strong>{{ $email ?? 'alamat Anda' }}</strong>.
        @if(isset($otp_code))
          (untuk pengujian: <strong>{{ $otp_code }}</strong>)
        @endif
      </p>

      <form method="POST" action="{{ route('otp.verify') }}">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <div class="mb-3">
          <label class="form-label">Kode OTP</label>
          <input name="otp" class="form-control" value="{{ old('otp') }}" required>
        </div>

        <div class="d-flex gap-2">
          <button class="btn btn-primary">Verifikasi</button>
          <a href="{{ route('otp.request') }}" class="btn btn-outline-secondary">Kirim Ulang</a>
        </div>
      </form>
    </div>
  </div>

@endsection
