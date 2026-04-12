@extends('layouts.auth')

@section('title','Verifikasi OTP')

@section('content')
  <style>
    #otpInputs {
      display: grid;
      grid-template-columns: repeat(6, minmax(0, 1fr));
      gap: 10px;
      width: 100%;
    }

    .otp-digit {
      width: 100%;
      min-width: 0;
      max-width: none;
      height: 56px;
      padding: 0 !important;
      border: 1px solid #d9dee5;
      border-radius: 2px;
      box-sizing: border-box;
      text-align: center;
      font-size: 1.75rem;
      font-weight: 700;
      line-height: 56px;
      color: #1f2937 !important;
      background-color: #ffffff !important;
      caret-color: #1f2937;
      -webkit-text-fill-color: #1f2937;
      outline: none;
      appearance: textfield;
    }

    .otp-digit:focus {
      border-color: #86b7fe;
      box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.12);
    }

    @media (max-width: 480px) {
      #otpInputs {
        gap: 8px;
      }

      .otp-digit {
        height: 50px;
        font-size: 1.5rem;
        line-height: 50px;
      }
    }
  </style>

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
        <input type="hidden" name="otp" id="otpCombined" value="{{ old('otp') }}">
        <div class="form-group">
          <label>Kode OTP</label>
          <div id="otpInputs">
            @for ($i = 0; $i < 6; $i++)
              <input
                type="tel"
                class="otp-digit"
                maxlength="1"
                inputmode="numeric"
                autocomplete="one-time-code"
                pattern="[0-9]"
                value="{{ substr(old('otp', ''), $i, 1) }}"
                aria-label="Digit OTP {{ $i + 1 }}"
                required
              >
            @endfor
          </div>
        </div>

        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-gradient-primary btn-lg">Verifikasi</button>
          <a href="{{ route('otp.request') }}" class="btn btn-outline-secondary">Kirim Ulang</a>
        </div>
      </form>
    </div>
  </div>

@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const otpInputs = Array.from(document.querySelectorAll('.otp-digit'));
    const otpCombined = document.getElementById('otpCombined');
    const form = otpCombined ? otpCombined.closest('form') : null;

    if (!otpInputs.length || !otpCombined || !form) {
      return;
    }

    const buildOtp = () => otpInputs.map((input) => input.value).join('');

    const syncOtp = () => {
      otpCombined.value = buildOtp();
    };

    otpInputs.forEach((input, index) => {
      input.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 1);
        syncOtp();

        if (this.value && index < otpInputs.length - 1) {
          otpInputs[index + 1].focus();
          otpInputs[index + 1].select();
        }
      });

      input.addEventListener('keydown', function (event) {
        if (event.key === 'Backspace' && !this.value && index > 0) {
          otpInputs[index - 1].focus();
          otpInputs[index - 1].select();
        }

        if (event.key === 'ArrowLeft' && index > 0) {
          event.preventDefault();
          otpInputs[index - 1].focus();
        }

        if (event.key === 'ArrowRight' && index < otpInputs.length - 1) {
          event.preventDefault();
          otpInputs[index + 1].focus();
        }
      });

      input.addEventListener('paste', function (event) {
        event.preventDefault();
        const pasted = (event.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);

        pasted.split('').forEach((digit, i) => {
          if (otpInputs[i]) {
            otpInputs[i].value = digit;
          }
        });

        syncOtp();

        const nextIndex = Math.min(pasted.length, otpInputs.length - 1);
        otpInputs[nextIndex].focus();
        otpInputs[nextIndex].select();
      });
    });

    form.addEventListener('submit', function (event) {
      syncOtp();

      if (otpCombined.value.length !== 6) {
        event.preventDefault();
        otpInputs.find((input) => !input.value)?.focus();
      }
    });

    syncOtp();
  });
</script>
@endpush
