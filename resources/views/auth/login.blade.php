@extends('layouts.auth')

@section('title','Login')

@section('content')
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Sign In</h4>
            <p class="card-description">Masuk ke akun Anda</p>

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="form-group">
                    <label>Email</label>
                    <input name="email" type="email" value="{{ old('email') }}" class="form-control" required autofocus>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input name="password" type="password" class="form-control" required>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-gradient-primary btn-lg">Sign In</button>
                    <a href="{{ url('auth/google') }}" class="btn btn-outline-danger">Login dengan Google</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-secondary">Belum punya akun? Register</a>
                </div>
            </form>
        </div>
    </div>
@endsection
