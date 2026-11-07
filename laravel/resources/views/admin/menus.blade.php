@extends('layouts.app')

@section('title', 'Kelola Menu | Panganin')

@section('content')
  <div class="section-header">
    <span class="section-tag">Admin</span>
    <h1 class="section-title">Kelola Menu Kantin</h1>
    <p class="section-subtitle">Tambah, perbarui, atau hapus menu di katalog Panganin.</p>
  </div>
  <section class="checkout-box" style="margin-bottom: 32px;">
    <h2 class="box-title">{{ $editingMenu ? 'Edit Menu' : 'Tambah Menu' }}</h2>
    <form class="checkout-form" method="post" action="{{ $editingMenu ? route('admin.menus.update', $editingMenu) : route('admin.menus.store') }}">
      @csrf
      @if ($editingMenu)
        @method('put')
      @endif
      @if ($errors->any())
        <div class="alert-box alert-danger" role="alert">{{ $errors->first() }}</div>
      @endif
      <div class="form-group">
        <label class="form-label" for="nama_menu">Nama menu</label>
        <input class="form-input" id="nama_menu" name="nama_menu" maxlength="150" value="{{ old('nama_menu', $editingMenu?->nama_menu) }}" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="kategori">Kategori</label>
        <input class="form-input" id="kategori" name="kategori" list="menu-categories" maxlength="100" value="{{ old('kategori', $editingMenu?->kategori) }}" required>
        <datalist id="menu-categories">@foreach ($categories as $category)<option value="{{ $category }}">@endforeach</datalist>
      </div>
      <div class="form-group">
        <label class="form-label" for="harga">Harga (rupiah)</label>
        <input class="form-input" id="harga" name="harga" type="number" min="1" value="{{ old('harga', $editingMenu?->harga) }}" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="gambar">URL gambar (HTTPS)</label>
        <input class="form-input" id="gambar" name="gambar" type="url" maxlength="2048" value="{{ old('gambar', $editingMenu?->gambar) }}" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="deskripsi">Deskripsi</label>
        <textarea class="form-input form-textarea" id="deskripsi" name="deskripsi" rows="3" maxlength="2000" required>{{ old('deskripsi', $editingMenu?->deskripsi) }}</textarea>
      </div>
      <button type="submit" class="btn btn-primary">{{ $editingMenu ? 'Simpan Perubahan' : 'Tambah Menu' }}</button>
      @if ($editingMenu)
        <a class="btn btn-secondary btn-sm" href="{{ route('admin.menus') }}">Batal</a>
      @endif
    </form>
  </section>
  <div class="order-history-list">
    @forelse ($menus as $menu)
      <article class="order-history-card">
        <div class="order-card-header">
          <div>
            <h2 class="order-number">{{ $menu->nama_menu }}</h2>
            <p class="order-timestamp">{{ $menu->kategori }}</p>
          </div>
          <span class="total-price">Rp{{ number_format($menu->harga, 0, ',', '.') }}</span>
        </div>
        <p>{{ $menu->deskripsi }}</p>
        <div class="order-history-actions">
          <a class="btn btn-secondary btn-sm" href="{{ route('admin.menus', ['edit' => $menu->id]) }}">Edit</a>
          <form method="post" action="{{ route('admin.menus.delete', $menu) }}" onsubmit="return confirm('Hapus menu ini?')">
            @csrf
            @method('delete')
            <button type="submit" class="btn-text-danger">Hapus</button>
          </form>
        </div>
      </article>
    @empty
      <p>Belum ada menu di database.</p>
    @endforelse
  </div>
@endsection
