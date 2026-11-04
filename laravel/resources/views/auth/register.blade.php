@extends('layouts.app')

@section('title', 'Daftar | Panganin')

@section('content')
  <div class="section-header">
    <span class="section-tag">Akun Panganin</span>
    <h1 class="section-title">Daftar Akun</h1>
    <p class="section-subtitle">Buat akun pelanggan untuk memesan dan melihat riwayat pesanan.</p>
  </div>
  <section class="checkout-box" style="max-width: 560px; margin: 0 auto;">
    <form class="checkout-form" method="post" action="{{ route('register.store') }}">
      @csrf
      @if ($errors->any())
        <div class="alert-box alert-danger" role="alert">{{ $errors->first() }}</div>
      @endif
      <div class="form-group">
        <label class="form-label" for="nama">Nama</label>
        <input class="form-input" id="nama" name="nama" value="{{ old('nama') }}" autocomplete="name" maxlength="120" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="email">Email</label>
        <input class="form-input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Kata sandi</label>
        <input class="form-input" id="password" name="password" type="password" autocomplete="new-password" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="password_confirmation">Ulangi kata sandi</label>
        <input class="form-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Daftar</button>
      <p class="form-hint">Sudah memiliki akun? <a href="{{ route('login') }}">Masuk</a>.</p>
    </form>
  </section>
@endsection
