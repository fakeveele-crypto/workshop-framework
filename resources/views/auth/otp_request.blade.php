@extends('layouts.app')

@section('title','Minta OTP')

@section('content')
  <div class="page-header">
    <h3 class="page-title">Minta Kode OTP</h3>
  </div>

  <div class="card">
    <div class="card-body" style="max-width:420px">
      @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
      @endif
      @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
      @endif

      <form method="POST" action="{{ route('otp.send') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input name="email" type="email" value="{{ old('email') }}" class="form-control" required>
        </div>

        <div class="d-flex gap-2">
          <button class="btn btn-primary">Kirim Kode OTP</button>
        </div>
      </form>
    </div>
  </div>

@endsection
