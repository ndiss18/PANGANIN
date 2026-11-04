@extends('layouts.app')

@section('title', $adminLogin ? 'Login Admin | Panganin' : 'Masuk | Panganin')

@section('content')
  <div class="section-header">
    <span class="section-tag">{{ $adminLogin ? 'Pengelolaan Kantin' : 'Akun Panganin' }}</span>
    <h1 class="section-title">{{ $adminLogin ? 'Login Admin' : 'Masuk ke Panganin' }}</h1>
    <p class="section-subtitle">Gunakan akun Panganin untuk menyimpan dan melihat pesanan.</p>
  </div>
  <section class="checkout-box" style="max-width: 560px; margin: 0 auto;">
    <form class="checkout-form" method="post" action="{{ $adminLogin ? route('admin.login.submit') : route('login.submit') }}">
      @csrf
      @if ($errors->any())
        <div class="alert-box alert-danger" role="alert">{{ $errors->first() }}</div>
      @endif
      <div class="form-group">
        <label class="form-label" for="email">Email</label>
        <input class="form-input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Kata sandi</label>
        <input class="form-input" id="password" name="password" type="password" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Masuk</button>
      @unless ($adminLogin)
        <p class="form-hint">Belum punya akun? <a href="{{ route('register') }}">Daftar akun pelanggan</a>.</p>
      @endunless
    </form>
  </section>
@endsection
