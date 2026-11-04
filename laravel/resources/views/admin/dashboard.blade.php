@extends('layouts.app')

@section('title', 'Dashboard Admin | Panganin')

@section('content')
  <div class="section-header">
    <span class="section-tag">Admin</span>
    <h1 class="section-title">Dashboard Panganin</h1>
    <p class="section-subtitle">Kelola menu dan pesanan kantin dari satu tempat.</p>
  </div>
  <div class="order-history-list">
    <article class="order-history-card"><h2 class="box-title">Menu tersedia</h2><p class="total-price">{{ $menuCount }}</p></article>
    <article class="order-history-card"><h2 class="box-title">Pesanan tersimpan</h2><p class="total-price">{{ $orderCount }}</p></article>
    <article class="order-history-card"><h2 class="box-title">Menunggu diproses</h2><p class="total-price">{{ $pendingCount }}</p></article>
  </div>
  <p>
    <a class="btn btn-primary" href="{{ route('admin.menus') }}">Kelola Menu</a>
    <a class="btn btn-secondary" href="{{ route('admin.orders') }}">Kelola Pesanan</a>
  </p>
@endsection
