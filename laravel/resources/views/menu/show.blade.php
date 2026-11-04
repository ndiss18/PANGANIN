@extends('layouts.app')

@section('title', $menu->nama_menu.' | Panganin')

@section('content')
  <article class="checkout-box" style="max-width: 760px; margin: 0 auto;">
    <img src="{{ $menu->gambar }}" alt="{{ $menu->nama_menu }}" style="width: 100%; max-height: 360px; object-fit: cover; border-radius: 16px;">
    <p class="section-tag">{{ $menu->kategori }}</p>
    <h1 class="box-title">{{ $menu->nama_menu }}</h1>
    <p>{{ $menu->deskripsi }}</p>
    <p class="total-price">Rp{{ number_format($menu->harga, 0, ',', '.') }}</p>
    <a class="btn btn-secondary btn-sm" href="{{ route('home') }}#menu">Kembali ke katalog</a>
  </article>
@endsection
