<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panganin | Kantin Kampus Digital</title>
  
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- CSS Utama -->
  <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>
<body
  data-menu-api-url="{{ route('api.products') }}"
  data-cart-api-url="{{ route('api.cart') }}"
  data-order-api-url="{{ route('api.orders') }}"
  data-order-url-prefix="{{ url('/orders') }}"
  data-session-api-url="{{ route('api.session') }}"
  data-menu-url-prefix="{{ url('/menu') }}"
>

  <!-- ================================================
       1. HEADER & NAVIGASI
       ====================== -->
  <header class="site-header">
    <div class="container header-container">
      <!-- Logo Brand -->
      <a href="#home" class="brand">
        <span class="brand-icon" aria-hidden="true">
          <svg viewBox="0 0 40 40" fill="none">
            <path d="M8 21h24l-2.1 9.2a3 3 0 0 1-2.9 2.3H13a3 3 0 0 1-2.9-2.3L8 21Z" fill="currentColor"/>
            <path d="M6 17.5h28M11 13c1.4-1.6 1.4-3 0-4.5M20 13c1.4-1.6 1.4-3 0-4.5M29 13c1.4-1.6 1.4-3 0-4.5" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>
          </svg>
        </span>
        <div class="brand-info">
          <span class="brand-name">Panganin</span>
          <span class="brand-tagline">Pesan makanan tanpa antre</span>
        </div>
      </a>

      <!-- Menu Navigasi -->
      <nav class="site-nav" aria-label="Navigasi Utama">
        <ul class="nav-list">
          <li class="nav-item">
            <a href="#home" class="nav-link active">Home</a>
          </li>
          <li class="nav-item">
            <a href="#menu" class="nav-link">Menu</a>
          </li>
          <li class="nav-item">
            <a href="#keranjang" class="nav-link nav-cart-btn">
              <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
                <path d="M3 4h2l2.2 11.1a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 2-1.6L22 8H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="10" cy="20" r="1.3" fill="currentColor"/>
                <circle cx="18" cy="20" r="1.3" fill="currentColor"/>
              </svg>
              <span>Keranjang</span>
              <span id="nav-cart-badge" class="cart-badge">0</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="#riwayat-pesanan" class="nav-link">Riwayat Pesanan</a>
          </li>
        </ul>
      </nav>
    </div>
  </header>

  <main>
    <!-- ========================================================
         2. HERO SECTION
         ======================================================== -->
    <section id="home" class="hero-section">
      <div class="container hero-container">
        <!-- Teks Hero -->
        <div class="hero-content">
          <span class="hero-pill"><span class="hero-pill-mark"></span> KANTIN TEKNIK</span>
          <h1 class="hero-title">Waktu istirahat,<br>lebih <span class="highlight-text">nikmat.</span></h1>
          <p class="hero-description">
            Temukan menu favorit dari kantin kampus, pesan lebih dulu, lalu nikmati tanpa perlu mengantre.
          </p>
          <div class="hero-action-group">
            <a href="#menu" class="btn btn-primary">Jelajahi menu <span aria-hidden="true">→</span></a>
          </div>

          <!-- Fitur Singkat -->
          <div class="hero-features">
            <div class="feature-item">
              <span class="feature-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="13" r="8" stroke="currentColor" stroke-width="1.7"/><path d="M12 9v4l2.5 1.5M9 2h6M12 2v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </span>
              <div>
                <strong>Pesan lebih dulu</strong>
                <p>Waktu istirahat lebih lega</p>
              </div>
            </div>
            <div class="feature-item">
              <span class="feature-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="M3 10h18M7 15h3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
              </span>
              <div>
                <strong>Bayar praktis</strong>
                <p>Pilih tunai atau QRIS</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Banner Visual Hero -->
        <div class="hero-visual">
          <div class="hero-card-preview">
            <div class="preview-badge"><span class="preview-badge-mark"></span> PILIHAN HARI INI</div>
            <img 
            src="https://i.pinimg.com/1200x/44/fd/03/44fd030f4ef3534963681dc98fcb7926.jpg"
              alt="Ayam Geprek Spesial Kantin" 
              class="preview-img"
            >
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ========================================================
         3. MENU KANTIN SECTION
         ======================================================== -->
    <section id="menu" class="menu-section">
      <div class="container">
        <!-- Judul Bagian Menu -->
        <div class="section-header">
          <span class="section-tag">Daftar Pilihan</span>
          <h2 class="section-title">Menu Kantin Kampus</h2>
          <p class="section-subtitle">
            Pilihan hidangan lezat dan minuman segar dengan harga terjangkau untuk mahasiswa.
          </p>
        </div>

        <!-- Filter Kategori Menu -->
        <div class="menu-filter-bar">
          <button type="button" class="filter-btn active" data-category="all">Semua Menu</button>
          <button type="button" class="filter-btn" data-category="makanan">Makanan</button>
          <button type="button" class="filter-btn" data-category="minuman">Minuman</button>
          <button type="button" class="filter-btn" data-category="camilan">Camilan</button>
          <input
            type="search"
            id="menu-search"
            class="menu-search-input"
            placeholder="Cari menu"
            aria-label="Cari menu makanan"
          >
        </div>

        <div class="menu-status-container">
          <p id="menu-status" class="menu-status" role="status" aria-live="polite" aria-busy="true">
            Memuat katalog makanan...
          </p>
          <button type="button" id="menu-retry-button" class="btn btn-secondary btn-sm hidden">
            Coba lagi
          </button>
        </div>

        <!-- Container Grid Kartu Menu (Dirender oleh script.js) -->
        <div id="menu-grid" class="menu-grid">
          <!-- Item menu akan digenerate secara otomatis oleh JavaScript -->
        </div>
      </div>
    </section>

    <!-- ========================================================
         4. KERANJANG & CHECKOUT SECTION
         ======================================================== -->
    @include('cart.index')

    @include('orders.history')
  </main>

  <!-- ========================================================
       6. FOOTER
       ======================================================== -->
  <footer class="site-footer">
    <div class="container footer-container">
      <!-- Kolom Brand -->
      <div class="footer-col brand-col">
        <div class="footer-brand">
          <span class="footer-logo-icon" aria-hidden="true">
            <svg viewBox="0 0 40 40" fill="none"><path d="M8 21h24l-2.1 9.2a3 3 0 0 1-2.9 2.3H13a3 3 0 0 1-2.9-2.3L8 21Z" fill="currentColor"/><path d="M6 17.5h28M11 13c1.4-1.6 1.4-3 0-4.5M20 13c1.4-1.6 1.4-3 0-4.5M29 13c1.4-1.6 1.4-3 0-4.5" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/></svg>
          </span>
          <span class="footer-logo-title">Panganin</span>
        </div>
        <p class="footer-desc">
          Platform pemesanan makanan kantin kampus modern yang memudahkan mahasiswa dan dosen menikmati hidangan hangat tanpa antre berdesakan.
        </p>
      </div>

      <!-- Kolom Navigasi -->
      <div class="footer-col links-col">
        <h4 class="footer-heading">Navigasi</h4>
        <ul class="footer-links">
          <li><a href="#home">Home</a></li>
          <li><a href="#menu">Daftar Menu</a></li>
          <li><a href="#keranjang">Keranjang & Checkout</a></li>
          <li><a href="#riwayat-pesanan">Riwayat Pesanan</a></li>
        </ul>
      </div>

      <!-- Kolom Layanan Kantin -->
      <div class="footer-col info-col">
        <h4 class="footer-heading">Layanan Kantin</h4>
        <ul class="footer-info-list">
          <li><svg aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.7"/></svg>Kantin Teknik</li>
          <li><svg aria-hidden="true" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>Jam Buka: 07.30 - 17.00 WIB (Senin - Jumat)</li>
          <li><svg aria-hidden="true" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>Layanan Bantuan: support@panganin.ac.id</li>
        </ul>
      </div>
    </div>

    <!-- Garis Hak Cipta -->
    <div class="footer-bottom">
      <div class="container footer-bottom-container">
        <p>2026 Panganin. Hak Cipta Dilindungi.</p>
        <p class="footer-credit">Dibuat khusus untuk kenyamanan kuliner mahasiswa kampus.</p>
      </div>
    </div>
  </footer>

  <!-- Toast Notification Container untuk Feedback Visual -->
  <div id="toast-container" class="toast-container" aria-live="polite"></div>

  <!-- Integrasi data menu dengan Fetch API -->
  <script src="{{ asset('api.js') }}"></script>
  <!-- JavaScript Utama -->
  <script src="{{ asset('script.js') }}"></script>
</body>
</html>
