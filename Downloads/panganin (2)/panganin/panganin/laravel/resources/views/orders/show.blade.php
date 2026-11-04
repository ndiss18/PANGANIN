@extends('layouts.app')

@section('title', 'Pesanan '.$order->nomor_pesanan.' | Panganin')

@section('content')
  <div class="section-header">
    <span class="section-tag">Rincian Pesanan</span>
    <h1 class="section-title">{{ $order->nomor_pesanan }}</h1>
    <p class="section-subtitle">Status: {{ str_replace('_', ' ', ucfirst($order->status)) }}</p>
  </div>
  <article class="order-history-card">
    <p>Pemesan: <strong>{{ $order->nama_pelanggan }}</strong></p>
    <p>Lokasi pengambilan: {{ $order->lokasi_pengambilan }}</p>
    @if ($order->catatan)<p>Catatan: {{ $order->catatan }}</p>@endif
    @foreach ($order->details as $detail)
      <div class="order-mini-item">
        <span class="order-mini-item-name">{{ $detail->nama_menu }} x{{ $detail->jumlah }}</span>
        <span class="order-mini-item-price">Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</span>
      </div>
    @endforeach
    <p class="total-price">Total: Rp{{ number_format($order->total_harga, 0, ',', '.') }}</p>
  </article>
  <a class="btn btn-secondary btn-sm" href="{{ route('home') }}#riwayat-pesanan">Kembali ke riwayat</a>
@endsection
