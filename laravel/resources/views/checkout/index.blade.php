          <!-- Sisi Kanan: Form Pemesanan & Ringkasan Checkout -->
          <aside class="checkout-box">
            <h3 class="box-title">Form Pemesanan Mahasiswa</h3>
            <p id="account-status" class="form-hint">Memeriksa sesi akun...</p>
            <div id="account-actions" class="form-group">
              <a id="login-link" class="btn btn-secondary btn-sm" href="{{ route('login') }}">Masuk</a>
              <a id="register-link" class="btn btn-secondary btn-sm" href="{{ route('register') }}">Daftar</a>
              <a id="management-link" class="btn btn-secondary btn-sm hidden" href="{{ route('admin.dashboard') }}">Dashboard Admin</a>
              <form id="logout-form" action="{{ route('logout') }}" method="post" class="hidden">
                <input id="logout-csrf-token" type="hidden" name="_token" value="{{ csrf_token() }}">
                <button type="submit" class="btn-text-danger">Keluar</button>
              </form>
            </div>
            
            <!-- Pesan Notifikasi Validasi Form -->
            <div id="checkout-error" class="alert-box alert-danger hidden"></div>

            <form id="checkout-form" class="checkout-form" novalidate>
              <div class="form-group">
                <label for="student-name" class="form-label">Nama Mahasiswa <span class="required">*</span></label>
                <input 
                  type="text" 
                  id="student-name" 
                  class="form-input" 
                  placeholder="Contoh: Dimas Aditya" 
                  required
                >
              </div>

              <div class="form-group">
                <label for="pickup-location" class="form-label">Nomor Meja / Lokasi Pengambilan <span class="required">*</span></label>
                <input 
                  type="text" 
                  id="pickup-location" 
                  class="form-input" 
                  placeholder="Contoh: Meja 14 (Kantek) / Ambil Langsung" 
                  required
                >
              </div>

              <div class="form-group">
                <label for="order-notes" class="form-label">Catatan Tambahan (Opsional)</label>
                <textarea 
                  id="order-notes" 
                  class="form-input form-textarea" 
                  rows="2" 
                  placeholder="Contoh: Sambal dipisah, jangan pakai seledri, dsb."
                ></textarea>
              </div>

              <!-- Rincian Biaya -->
              <div class="checkout-summary">
                <div class="summary-line">
                  <span>Total Item</span>
                  <span id="summary-total-items" class="fw-bold">0 item</span>
                </div>
                <div class="summary-line">
                  <span>Biaya Layanan</span>
                  <span class="badge-free">Gratis</span>
                </div>
                <div class="summary-line total-line">
                  <span class="total-label">Total Pembayaran</span>
                  <span id="summary-total-price" class="total-price">Rp0</span>
                </div>
              </div>

              <!-- Tombol Checkout -->
              <button type="submit" id="btn-submit-order" class="btn btn-primary btn-block btn-lg">
                Pesan Sekarang
              </button>
              <p class="form-hint">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none"><rect x="4" y="10" width="16" height="11" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                Pembayaran dapat dilakukan via QRIS atau Tunai di kasir stand saat pesanan siap diambil.
              </p>
            </form>
          </aside>