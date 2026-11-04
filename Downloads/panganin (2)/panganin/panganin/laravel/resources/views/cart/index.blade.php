    <section id="keranjang" class="cart-section">
      <div class="container">
        <div class="section-header">
          <span class="section-tag">Konfirmasi Pesanan</span>
          <h2 class="section-title">Keranjang & Checkout</h2>
          <p class="section-subtitle">
            Kelola porsi makananmu dan lengkapi data pemesanan untuk langsung diproses kantin.
          </p>
        </div>

        <div class="cart-checkout-layout">
          <!-- Sisi Kiri: Daftar Keranjang Makanan -->
          <div class="cart-box">
            <div class="cart-box-header">
              <h3 class="box-title">Daftar Item Terpilih</h3>
              <button type="button" id="btn-clear-cart" class="btn-text-danger" title="Kosongkan seluruh isi keranjang">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M10 11v6m4-6v6M6 7l1 13h10l1-13M9 7V4h6v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Kosongkan
              </button>
            </div>

            <!-- Tampilan Jika Keranjang Kosong -->
            <div id="cart-empty-state" class="cart-empty-state">
              <div class="empty-icon" aria-hidden="true">
                <svg viewBox="0 0 48 48" fill="none"><path d="M7 10h4l4.2 20.1a3 3 0 0 0 2.9 2.4h17.1a3 3 0 0 0 2.9-2.4L42 17H13" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="20" cy="39" r="2.5" fill="currentColor"/><circle cx="35" cy="39" r="2.5" fill="currentColor"/></svg>
              </div>
              <h4>Keranjangmu masih kosong</h4>
              <p>Yuk pilih santapan favoritmu di bagian menu di atas!</p>
              <a href="#menu" class="btn btn-secondary btn-sm">Mulai Pilih Menu</a>
            </div>

            <!-- Kontainer Daftar Item Keranjang -->
            <div id="cart-items-container" class="cart-items-container hidden">
              <div class="cart-table-wrapper">
                <table class="cart-table">
                  <thead>
                    <tr>
                      <th>Menu</th>
                      <th>Harga</th>
                      <th class="text-center">Jumlah</th>
                      <th class="text-right">Subtotal</th>
                      <th class="text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody id="cart-table-body">
                    <!-- Baris item keranjang dirender dinamis di sini -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          @include('checkout.index')
        </div>
      </div>
    </section>

    <!-- ========================================================
         5. RIWAYAT PESANAN SECTION
         ======================================================== -->