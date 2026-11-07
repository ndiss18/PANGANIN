@extends('layouts.app')

@section('title', 'Kelola Pesanan | Panganin')

@section('content')
  <div class="section-header">
    <span class="section-tag">Admin</span>
    <h1 class="section-title">Pesanan Pelanggan</h1>
    <p class="section-subtitle">Periksa rincian pesanan dan perbarui status pemrosesan.</p>
  </div>
  <div class="order-history-list">
    @forelse ($orders as $order)
      <article class="order-history-card">
        <div class="order-card-header">
          <div>
            <h2 class="order-number">{{ $order->nomor_pesanan }}</h2>
            <p class="order-timestamp">{{ $order->created_at->format('d M Y, H:i') }} · {{ $order->nama_pelanggan }} · {{ $order->lokasi_pengambilan }}</p>
          </div>
          <span class="total-price">Rp{{ number_format($order->total_harga, 0, ',', '.') }}</span>
        </div>
        <div class="order-details-box">
          @foreach ($order->details as $detail)
            <div class="order-mini-item">
              <span class="order-mini-item-name">{{ $detail->nama_menu }} <strong>x{{ $detail->jumlah }}</strong></span>
              <span class="order-mini-item-price">Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</span>
            </div>
          @endforeach
          @if ($order->catatan)
            <p class="order-notes-tag">Catatan: {{ $order->catatan }}</p>
          @endif
        </div>
        <form class="checkout-form" method="post" action="{{ route('admin.orders.status', $order) }}">
          @csrf
          @method('patch')
          <div class="form-group">
            <label class="form-label" for="status-{{ $order->id }}">Status pesanan</label>
            <select class="form-input" id="status-{{ $order->id }}" name="status">
              @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected($order->status === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
              @endforeach
            </select>
          </div>
          <button class="btn btn-secondary btn-sm" type="submit">Perbarui Status</button>
        </form>
      </article>
    @empty
      <p>Belum ada pesanan pelanggan.</p>
    @endforelse
  </div>
@endsection
