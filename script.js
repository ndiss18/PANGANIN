/**
 * ========================================================
 * PANGANIN - JAVASCRIPT UTAMA
 * Aplikasi Web Pemesanan Makanan Kantin Kampus
 * Vanilla JS (Tanpa Framework)
 * ========================================================
 */

// Menjalankan kode setelah DOM selesai dimuat
document.addEventListener('DOMContentLoaded', () => {

  /* ------------------------------------------------------
     2. STATE MANAGEMENT (LOCAL STORAGE)
     ------------------------------------------------------ */
  let cart = loadFromLocalStorage('panganin_cart', []);
  let orders = loadFromLocalStorage('panganin_orders', []);
  let MENU_DATA = [];

  function loadFromLocalStorage(key, defaultValue) {
    try {
      const saved = localStorage.getItem(key);
      return saved ? JSON.parse(saved) : defaultValue;
    } catch (e) {
      console.error('Gagal membaca data dari localStorage:', e);
      return defaultValue;
    }
  }

  function saveToLocalStorage(key, value) {
    try {
      localStorage.setItem(key, JSON.stringify(value));
    } catch (e) {
      console.error('Gagal menyimpan data ke localStorage:', e);
    }
  }

  /* ------------------------------------------------------
     3. HELPER FUNCTIONS
     ------------------------------------------------------ */
  // Format angka ke format Rupiah (e.g. 15000 -> Rp15.000)
  function formatRupiah(number) {
    return 'Rp' + Number(number).toLocaleString('id-ID');
  }

  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, character => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;'
    })[character]);
  }

  // Menampilkan Toast Notification
  function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    const icon = type === 'success'
      ? '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
      : type === 'danger'
        ? '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 2.8 19h18.4L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M12 9v4m0 3h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>'
        : '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 11v5m0-8h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';

    const iconElement = document.createElement('span');
    iconElement.className = 'toast-icon';
    iconElement.innerHTML = icon;

    const messageElement = document.createElement('span');
    messageElement.textContent = message;

    toast.append(iconElement, messageElement);
    container.appendChild(toast);

    // Animasi muncul
    setTimeout(() => {
      toast.classList.add('show');
    }, 10);

    // Otomatis hilang setelah 3.2 detik
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => {
        if (toast.parentElement) {
          toast.parentElement.removeChild(toast);
        }
      }, 300);
    }, 3200);
  }

  // Efek Bump pada Badge Keranjang
  function bumpCartBadge() {
    const badge = document.getElementById('nav-cart-badge');
    if (!badge) return;
    badge.classList.add('bump');
    setTimeout(() => badge.classList.remove('bump'), 250);
  }

  /* ------------------------------------------------------
     4. RENDER MENU MAKANAN & FILTER KATEGORI
     ------------------------------------------------------ */
  const menuGrid = document.getElementById('menu-grid');
  const filterBtns = document.querySelectorAll('.filter-btn');
  const searchInput = document.getElementById('menu-search');
  const CATEGORY_FILTERS = {
    makanan: ['Makanan berat', 'Mie dan bakso', 'Lauk dan gorengan'],
    minuman: ['Minuman'],
    camilan: ['Camilan']
  };
  let currentCategory = 'all';
  let currentSearch = '';

  function renderMenu(category = 'all') {
    if (!menuGrid) return;
    menuGrid.innerHTML = '';

    const categories = CATEGORY_FILTERS[category];
    const searchTerm = currentSearch.toLocaleLowerCase('id-ID');
    const filtered = MENU_DATA.filter(item => {
      const matchesCategory = category === 'all' || categories.includes(item.category);
      const searchableText = `${item.name} ${item.category} ${item.description}`.toLocaleLowerCase('id-ID');
      return matchesCategory && searchableText.includes(searchTerm);
    });

    filtered.forEach(item => {
      const card = document.createElement('article');
      card.className = 'menu-card';

      const media = document.createElement('div');
      media.className = 'card-media';

      const image = document.createElement('img');
      image.src = item.image;
      image.alt = item.name;
      image.className = 'card-img';
      image.loading = 'lazy';
      media.appendChild(image);

      const badge = document.createElement('span');
      const badgeClass = item.category === 'Minuman'
        ? 'beverage'
        : item.category === 'Camilan' || item.category === 'Lauk dan gorengan'
          ? 'snack'
          : '';
      badge.className = `category-badge ${badgeClass}`;
      badge.textContent = item.category;
      media.appendChild(badge);

      const body = document.createElement('div');
      body.className = 'card-body';

      const title = document.createElement('h3');
      title.className = 'card-title';
      title.textContent = item.name;

      const description = document.createElement('p');
      description.className = 'card-desc';
      description.textContent = item.description;

      const footer = document.createElement('div');
      footer.className = 'card-footer';

      const price = document.createElement('span');
      price.className = 'price-tag';
      price.textContent = formatRupiah(item.price);

      const addButton = document.createElement('button');
      addButton.type = 'button';
      addButton.className = 'btn btn-sm btn-primary btn-add-menu';
      addButton.dataset.id = String(item.id);
      addButton.textContent = 'Tambah';

      footer.append(price, addButton);
      body.append(title, description, footer);
      card.append(media, body);

      menuGrid.appendChild(card);
    });
  }

  document.addEventListener('panganin:menu-loaded', event => {
    MENU_DATA = event.detail;
    renderMenu(currentCategory);
  });

  if (menuGrid) {
    menuGrid.addEventListener('click', event => {
      const button = event.target.closest('.btn-add-menu');
      if (!button || !menuGrid.contains(button)) return;

      const item = MENU_DATA.find(menu => menu.id === Number(button.dataset.id));
      if (item) addToCart(item);
    });
  }

  // Filter Kategori Listener
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentCategory = btn.getAttribute('data-category');
      renderMenu(currentCategory);
    });
  });

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      currentSearch = searchInput.value.trim();
      renderMenu(currentCategory);
    });
  }

  /* ------------------------------------------------------
     5. KERANJANG BELANJA (CART)
     ------------------------------------------------------ */
  // Menambahkan item ke keranjang
  function addToCart(item) {
    if (!item || !Number.isFinite(item.price) || item.price <= 0) return;

    const cartItemId = `api-${item.id}`;
    const existingIndex = cart.findIndex(c => c.id === cartItemId);
    if (existingIndex > -1) {
      cart[existingIndex].qty += 1;
    } else {
      cart.push({
        id: cartItemId,
        name: item.name,
        price: item.price,
        image: item.image,
        qty: 1
      });
    }

    saveToLocalStorage('panganin_cart', cart);
    renderCart();
    bumpCartBadge();
    showToast(`"${item.name}" berhasil masuk ke keranjang!`, 'success');
  }

  function getCartItemId(button) {
    const id = button.getAttribute('data-id');
    return /^\d+$/.test(id) ? Number(id) : id;
  }

  // Mengubah kuantitas item (+ / -)
  function updateCartQty(menuId, delta) {
    const index = cart.findIndex(c => c.id === menuId);
    if (index === -1) return;

    cart[index].qty += delta;

    if (cart[index].qty <= 0) {
      const removedItem = cart[index].name;
      cart.splice(index, 1);
      showToast(`"${removedItem}" dihapus dari keranjang.`, 'info');
    }

    saveToLocalStorage('panganin_cart', cart);
    renderCart();
  }

  // Menghapus item secara langsung
  function removeFromCart(menuId) {
    const index = cart.findIndex(c => c.id === menuId);
    if (index === -1) return;

    const removedItem = cart[index].name;
    cart.splice(index, 1);
    saveToLocalStorage('panganin_cart', cart);
    renderCart();
    showToast(`"${removedItem}" telah dihapus.`, 'info');
  }

  // Mengosongkan seluruh keranjang
  const btnClearCart = document.getElementById('btn-clear-cart');
  if (btnClearCart) {
    btnClearCart.addEventListener('click', () => {
      if (cart.length === 0) return;
      if (confirm('Apakah kamu yakin ingin mengosongkan keranjang?')) {
        cart = [];
        saveToLocalStorage('panganin_cart', cart);
        renderCart();
        showToast('Keranjang telah dikosongkan.', 'info');
      }
    });
  }

  // Render Tampilan Keranjang
  function renderCart() {
    const emptyState = document.getElementById('cart-empty-state');
    const itemsContainer = document.getElementById('cart-items-container');
    const tableBody = document.getElementById('cart-table-body');
    const badge = document.getElementById('nav-cart-badge');
    const summaryTotalItems = document.getElementById('summary-total-items');
    const summaryTotalPrice = document.getElementById('summary-total-price');

    // Menghitung total jumlah & total harga
    const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
    const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);

    // Update Badge Navigasi
    if (badge) {
      badge.textContent = totalQty;
    }

    // Update Ringkasan Checkout
    if (summaryTotalItems) {
      summaryTotalItems.textContent = `${totalQty} item`;
    }
    if (summaryTotalPrice) {
      summaryTotalPrice.textContent = formatRupiah(totalPrice);
    }

    // Kondisi Keranjang Kosong vs Ada Isi
    if (cart.length === 0) {
      if (emptyState) emptyState.classList.remove('hidden');
      if (itemsContainer) itemsContainer.classList.add('hidden');
      if (btnClearCart) btnClearCart.style.visibility = 'hidden';
      return;
    }

    if (emptyState) emptyState.classList.add('hidden');
    if (itemsContainer) itemsContainer.classList.remove('hidden');
    if (btnClearCart) btnClearCart.style.visibility = 'visible';

    // Render Baris Tabel
    if (tableBody) {
      tableBody.innerHTML = '';
      cart.forEach(item => {
        const subtotal = item.price * item.qty;
        const tr = document.createElement('tr');
        tr.className = 'cart-item-row';
        tr.innerHTML = `
          <td>
            <div class="cart-item-info">
              <img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name)}" class="cart-thumb">
              <span class="item-name">${escapeHtml(item.name)}</span>
            </div>
          </td>
          <td class="unit-price">${formatRupiah(item.price)}</td>
          <td class="text-center">
            <div class="quantity-control">
              <button type="button" class="qty-btn btn-decrease" data-id="${escapeHtml(item.id)}" title="Kurangi">−</button>
              <span class="qty-number">${escapeHtml(item.qty)}</span>
              <button type="button" class="qty-btn btn-increase" data-id="${escapeHtml(item.id)}" title="Tambah">+</button>
            </div>
          </td>
          <td class="text-right subtotal-price">${formatRupiah(subtotal)}</td>
          <td class="text-center">
            <button type="button" class="btn-delete-item" data-id="${escapeHtml(item.id)}" title="Hapus makanan ini">
              <svg aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M10 11v6m4-6v6M6 7l1 13h10l1-13M9 7V4h6v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
          </td>
        `;

        tableBody.appendChild(tr);
      });

      // Hubungkan Event Listener pada tombol kuantitas & hapus
      tableBody.querySelectorAll('.btn-increase').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const id = getCartItemId(e.currentTarget);
          updateCartQty(id, 1);
        });
      });

      tableBody.querySelectorAll('.btn-decrease').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const id = getCartItemId(e.currentTarget);
          updateCartQty(id, -1);
        });
      });

      tableBody.querySelectorAll('.btn-delete-item').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const id = getCartItemId(e.currentTarget);
          removeFromCart(id);
        });
      });
    }
  }

  /* ------------------------------------------------------
     6. CHECKOUT & VALIDASI PEMESANAN
     ------------------------------------------------------ */
  const checkoutForm = document.getElementById('checkout-form');
  const errorBox = document.getElementById('checkout-error');

  function showCheckoutError(message) {
    if (!errorBox) return;
    errorBox.textContent = message;
    errorBox.classList.remove('hidden');
    // Scroll kecil ke error box jika perlu
    errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function hideCheckoutError() {
    if (!errorBox) return;
    errorBox.textContent = '';
    errorBox.classList.add('hidden');
  }

  if (checkoutForm) {
    checkoutForm.addEventListener('submit', (e) => {
      e.preventDefault();
      hideCheckoutError();

      // 1. Validasi isi keranjang
      if (cart.length === 0) {
        showCheckoutError('Keranjangmu masih kosong. Silakan tambahkan menu sebelum memesan.');
        showToast('Keranjang masih kosong!', 'danger');
        return;
      }

      // 2. Ambil nilai input
      const inputName = document.getElementById('student-name');
      const inputLocation = document.getElementById('pickup-location');
      const inputNotes = document.getElementById('order-notes');

      const name = inputName ? inputName.value.trim() : '';
      const location = inputLocation ? inputLocation.value.trim() : '';
      const notes = inputNotes ? inputNotes.value.trim() : '';

      // Reset border invalid
      [inputName, inputLocation].forEach(input => {
        if (input) input.classList.remove('is-invalid');
      });

      // 3. Validasi Field Wajib
      let hasError = false;
      let errorMsg = '';

      if (!name) {
        if (inputName) inputName.classList.add('is-invalid');
        hasError = true;
        errorMsg = 'Mohon isi Nama Mahasiswa.';
      } else if (!location) {
        if (inputLocation) inputLocation.classList.add('is-invalid');
        hasError = true;
        errorMsg = 'Mohon isi Nomor Meja atau Lokasi Pengambilan.';
      }

      if (hasError) {
        showCheckoutError(errorMsg);
        return;
      }

      // 4. Generate Nomor Pesanan Unik
      const timestamp = new Date();
      const randomNum = Math.floor(1000 + Math.random() * 9000);
      const orderNumber = `#PGN-${randomNum}`;

      // Format Waktu Indonesia (e.g. 12:45 WIB)
      const hours = String(timestamp.getHours()).padStart(2, '0');
      const minutes = String(timestamp.getMinutes()).padStart(2, '0');
      const timeString = `${hours}:${minutes} WIB`;

      // Menghitung Total
      const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);

      // 5. Buat Objek Pesanan Baru
      const newOrder = {
        id: Date.now(),
        orderNumber: orderNumber,
        createdAt: timeString,
        student: {
          name: name,
          location: location,
          notes: notes
        },
        items: [...cart], // salinan item saat checkout
        totalPrice: totalPrice
      };

      // Simpan pesanan terbaru di awal riwayat
      orders.unshift(newOrder);
      saveToLocalStorage('panganin_orders', orders);

      // 6. Kosongkan Keranjang & Reset Form
      cart = [];
      saveToLocalStorage('panganin_cart', cart);
      renderCart();
      checkoutForm.reset();

      // 7. Perbarui riwayat pesanan
      renderOrderHistory();
      showToast(`Pesanan ${orderNumber} berhasil dibuat!`, 'success');

      // Arahkan ke riwayat setelah checkout berhasil
      const orderHistorySection = document.getElementById('riwayat-pesanan');
      if (orderHistorySection) {
        orderHistorySection.scrollIntoView({ behavior: 'smooth' });
      }
    });
  }

  /* ------------------------------------------------------
     7. RIWAYAT PESANAN
     ------------------------------------------------------ */
  const orderHistoryEmpty = document.getElementById('order-history-empty');
  const orderHistoryList = document.getElementById('order-history-list');

  function deleteOrder(orderId) {
    if (!confirm('Hapus riwayat pesanan ini?')) return;
    orders = orders.filter(o => o.id !== orderId);
    saveToLocalStorage('panganin_orders', orders);
    renderOrderHistory();
    showToast('Pesanan telah dihapus dari daftar.', 'info');
  }

  function renderOrderHistory() {
    if (!orderHistoryEmpty || !orderHistoryList) return;

    if (orders.length === 0) {
      orderHistoryEmpty.classList.remove('hidden');
      orderHistoryList.classList.add('hidden');
      return;
    }

    orderHistoryEmpty.classList.add('hidden');
    orderHistoryList.classList.remove('hidden');
    orderHistoryList.innerHTML = '';

    orders.forEach(order => {
      const orderCard = document.createElement('article');
      orderCard.className = 'order-history-card';

      // Rincian Item Pesanan
      let itemsListHtml = '';
      order.items.forEach(item => {
        itemsListHtml += `
          <div class="order-mini-item">
            <span class="order-mini-item-name">${escapeHtml(item.name)} <strong>x${escapeHtml(item.qty)}</strong></span>
            <span class="order-mini-item-price">${formatRupiah(item.price * item.qty)}</span>
          </div>
        `;
      });

      orderCard.innerHTML = `
        <div class="order-card-header">
          <div>
            <div class="order-meta-title">
              <span class="order-number">${escapeHtml(order.orderNumber)}</span>
            </div>
            <p class="order-timestamp">Dipesan pada ${escapeHtml(order.createdAt)} • Pemesan: <strong>${escapeHtml(order.student.name)}</strong></p>
            <p class="order-location">Lokasi pengambilan: ${escapeHtml(order.student.location)}</p>
          </div>
          <div>
            <span class="total-label">Total Pembayaran: </span>
            <span class="total-price">${formatRupiah(order.totalPrice)}</span>
          </div>
        </div>

        <div class="order-details-box">
          <h5 class="order-details-title">Detail Pesanan:</h5>
          <div class="order-items-mini-list">
            ${itemsListHtml}
          </div>
          ${order.student.notes ? `<div class="order-notes-tag"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="m14 4 6 6M4 20l4.3-.9L19 8.4 15.6 5 4.9 15.7 4 20Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Catatan: "${escapeHtml(order.student.notes)}"</span></div>` : ''}
        </div>

        <div class="order-history-actions">
          <button
            type="button"
            class="btn-text-danger"
            data-order-delete-id="${order.id}"
            title="Hapus pesanan dari riwayat">
            Hapus dari riwayat
          </button>
        </div>
      `;

      orderHistoryList.appendChild(orderCard);
    });

    orderHistoryList.querySelectorAll('[data-order-delete-id]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const orderId = Number(e.currentTarget.getAttribute('data-order-delete-id'));
        deleteOrder(orderId);
      });
    });
  }

  /* ------------------------------------------------------
     8. NAVIGASI AKTIF BERDASARKAN SCROLL & KLIK
     ------------------------------------------------------ */
  const navLinks = document.querySelectorAll('.nav-link');
  const sections = document.querySelectorAll('section[id]');

  window.addEventListener('scroll', () => {
    let currentScrollSection = '';
    const scrollPosition = window.pageYOffset + 120;

    sections.forEach(section => {
      const sectionTop = section.offsetTop;
      const sectionHeight = section.offsetHeight;
      if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
        currentScrollSection = section.getAttribute('id');
      }
    });

    navLinks.forEach(link => {
      link.classList.remove('active');
      const href = link.getAttribute('href');
      if (href && href === `#${currentScrollSection}`) {
        link.classList.add('active');
      }
    });
  });

  /* ------------------------------------------------------
     9. INISIALISASI AWAL APLIKASI
     ------------------------------------------------------ */
  renderMenu();
  renderCart();
  renderOrderHistory();

  console.log('Panganin App berhasil diinisialisasi dengan data localStorage.');
});
