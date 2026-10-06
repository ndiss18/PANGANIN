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
     1. DATA MASTER MENU KANTIN
     ------------------------------------------------------ */
  const MENU_DATA = [
    {
      id: 1,
      name: 'Nasi Goreng',
      category: 'makanan',
      price: 15000,
      description: 'Nasi goreng bumbu gurih khas kantin kampus dengan telur mata sapi, suwiran ayam, dan kerupuk renyah.',
      image: 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=600&q=80',
      badge: 'Makanan'
    },
    {
      id: 2,
      name: 'Ayam Geprek',
      category: 'makanan',
      price: 18000,
      description: 'Ayam goreng tepung krispi digeprek dengan sambal bawang segar pedas mantap lengkap dengan nasi hangat.',
      image: 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=600&q=80',
      badge: 'Makanan'
    },
    {
      id: 3,
      name: 'Mie Ayam',
      category: 'makanan',
      price: 13000,
      description: 'Mie kenyal dengan potongan ayam kecap manis gurih, sawi hijau segar, taburan daun bawang, dan kuah kaldu.',
      image: 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=600&q=80',
      badge: 'Makanan'
    },
    {
      id: 4,
      name: 'Es Teh',
      category: 'minuman',
      price: 5000,
      description: 'Seduhan teh melati wangi dengan es batu melimpah. Segar dan pas untuk meredakan dahaga setelah kuliah.',
      image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=600&q=80',
      badge: 'Minuman'
    },
    {
      id: 5,
      name: 'Kopi',
      category: 'minuman',
      price: 8000,
      description: 'Kopi robusta racikan khas kantin dengan paduan susu manis creamy, ampuh usir kantuk saat kuliah pagi.',
      image: 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=600&q=80',
      badge: 'Minuman'
    },
    {
      id: 6,
      name: 'Roti Bakar',
      category: 'camilan',
      price: 10000,
      description: 'Roti panggang mentega dengan isian cokelat lumer dan parutan keju cheddar melimpah.',
      image: 'https://images.unsplash.com/photo-1584776296944-ab6fb57b0bdd?auto=format&fit=crop&w=600&q=80',
      badge: 'Camilan'
    }
  ];

  // Daftar tahapan status pesanan
  const ORDER_STATUS_STEPS = [
    {
      name: 'Pesanan Diterima',
      desc: 'Pesanan sudah masuk ke sistem dan dikonfirmasi kantin.',
      badgeText: 'Diterima'
    },
    {
      name: 'Sedang Diproses',
      desc: 'Makanan dan minuman sedang dimasak oleh penjual.',
      badgeText: 'Diproses'
    },
    {
      name: 'Siap Diambil',
      desc: 'Silakan ambil di stand kantin dengan menunjukkan ID Pesanan.',
      badgeText: 'Siap Diambil'
    }
  ];

  /* ------------------------------------------------------
     2. STATE MANAGEMENT (LOCAL STORAGE)
     ------------------------------------------------------ */
  let cart = loadFromLocalStorage('panganin_cart', []);
  let orders = loadFromLocalStorage('panganin_orders', []);

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

  // Menampilkan Toast Notification
  function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    let icon = '🔔';
    if (type === 'success') icon = '✅';
    if (type === 'danger') icon = '⚠️';

    toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
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
  let currentCategory = 'all';

  function renderMenu(category = 'all') {
    if (!menuGrid) return;
    menuGrid.innerHTML = '';

    const filtered = category === 'all' 
      ? MENU_DATA 
      : MENU_DATA.filter(item => item.category === category);

    filtered.forEach(item => {
      const card = document.createElement('article');
      card.className = 'menu-card';

      // Badge CSS modifier berdasarkan kategori
      let badgeClass = '';
      if (item.category === 'minuman') badgeClass = 'beverage';
      if (item.category === 'camilan') badgeClass = 'snack';

      card.innerHTML = `
        <div class="card-media">
          <img src="${item.image}" alt="${item.name}" class="card-img" loading="lazy">
          <span class="category-badge ${badgeClass}">${item.badge}</span>
        </div>
        <div class="card-body">
          <h3 class="card-title">${item.name}</h3>
          <p class="card-desc">${item.description}</p>
          <div class="card-footer">
            <span class="price-tag">${formatRupiah(item.price)}</span>
            <button type="button" class="btn btn-sm btn-primary btn-add-menu" data-id="${item.id}">
              + Tambah
            </button>
          </div>
        </div>
      `;

      menuGrid.appendChild(card);
    });

    // Menghubungkan event listener tombol "Tambah"
    const addBtns = menuGrid.querySelectorAll('.btn-add-menu');
    addBtns.forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = parseInt(e.currentTarget.getAttribute('data-id'), 10);
        addToCart(id);
      });
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

  /* ------------------------------------------------------
     5. KERANJANG BELANJA (CART)
     ------------------------------------------------------ */
  // Menambahkan item ke keranjang
  function addToCart(menuId) {
    const item = MENU_DATA.find(m => m.id === menuId);
    if (!item) return;

    const existingIndex = cart.findIndex(c => c.id === menuId);
    if (existingIndex > -1) {
      cart[existingIndex].qty += 1;
    } else {
      cart.push({
        id: item.id,
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
              <img src="${item.image}" alt="${item.name}" class="cart-thumb">
              <span class="item-name">${item.name}</span>
            </div>
          </td>
          <td class="unit-price">${formatRupiah(item.price)}</td>
          <td class="text-center">
            <div class="quantity-control">
              <button type="button" class="qty-btn btn-decrease" data-id="${item.id}" title="Kurangi">−</button>
              <span class="qty-number">${item.qty}</span>
              <button type="button" class="qty-btn btn-increase" data-id="${item.id}" title="Tambah">+</button>
            </div>
          </td>
          <td class="text-right subtotal-price">${formatRupiah(subtotal)}</td>
          <td class="text-center">
            <button type="button" class="btn-delete-item" data-id="${item.id}" title="Hapus makanan ini">
              🗑️
            </button>
          </td>
        `;

        tableBody.appendChild(tr);
      });

      // Hubungkan Event Listener pada tombol kuantitas & hapus
      tableBody.querySelectorAll('.btn-increase').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const id = parseInt(e.currentTarget.getAttribute('data-id'), 10);
          updateCartQty(id, 1);
        });
      });

      tableBody.querySelectorAll('.btn-decrease').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const id = parseInt(e.currentTarget.getAttribute('data-id'), 10);
          updateCartQty(id, -1);
        });
      });

      tableBody.querySelectorAll('.btn-delete-item').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const id = parseInt(e.currentTarget.getAttribute('data-id'), 10);
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
        showCheckoutError('⚠️ Keranjangmu masih kosong! Silakan tambahkan menu sebelum memesan.');
        showToast('Keranjang masih kosong!', 'danger');
        return;
      }

      // 2. Ambil nilai input
      const inputName = document.getElementById('student-name');
      const inputNim = document.getElementById('student-nim');
      const inputLocation = document.getElementById('pickup-location');
      const inputNotes = document.getElementById('order-notes');

      const name = inputName ? inputName.value.trim() : '';
      const nim = inputNim ? inputNim.value.trim() : '';
      const location = inputLocation ? inputLocation.value.trim() : '';
      const notes = inputNotes ? inputNotes.value.trim() : '';

      // Reset border invalid
      [inputName, inputNim, inputLocation].forEach(input => {
        if (input) input.classList.remove('is-invalid');
      });

      // 3. Validasi Field Wajib
      let hasError = false;
      let errorMsg = '';

      if (!name) {
        if (inputName) inputName.classList.add('is-invalid');
        hasError = true;
        errorMsg = 'Mohon isi Nama Mahasiswa.';
      } else if (!nim) {
        if (inputNim) inputNim.classList.add('is-invalid');
        hasError = true;
        errorMsg = 'Mohon isi NIM (Nomor Induk Mahasiswa).';
      } else if (!location) {
        if (inputLocation) inputLocation.classList.add('is-invalid');
        hasError = true;
        errorMsg = 'Mohon isi Nomor Meja atau Lokasi Pengambilan.';
      }

      if (hasError) {
        showCheckoutError(`⚠️ ${errorMsg}`);
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
          nim: nim,
          location: location,
          notes: notes
        },
        items: [...cart], // salinan item saat checkout
        totalPrice: totalPrice,
        statusIndex: 0 // Awal: 0 = Pesanan Diterima
      };

      // Tambahkan ke urutan paling depan array orders
      orders.unshift(newOrder);
      saveToLocalStorage('panganin_orders', orders);

      // 6. Kosongkan Keranjang & Reset Form
      cart = [];
      saveToLocalStorage('panganin_cart', cart);
      renderCart();
      checkoutForm.reset();

      // 7. Render Ulang Pesanan Saya & Arahkan Tampilan
      renderOrders();
      showToast(`Pesanan ${orderNumber} berhasil dibuat!`, 'success');

      // Scroll mulus ke bagian Pesanan Saya
      const ordersSection = document.getElementById('pesanan');
      if (ordersSection) {
        ordersSection.scrollIntoView({ behavior: 'smooth' });
      }
    });
  }

  /* ------------------------------------------------------
     7. STATUS PESANAN (PESANAN SAYA) & SIMULASI DEMO
     ------------------------------------------------------ */
  const ordersEmptyState = document.getElementById('orders-empty-state');
  const ordersListContainer = document.getElementById('orders-list-container');

  // Mengubah status pesanan secara bertahap (Demo)
  function advanceOrderStatus(orderId) {
    const order = orders.find(o => o.id === orderId);
    if (!order) return;

    order.statusIndex = (order.statusIndex + 1) % ORDER_STATUS_STEPS.length;
    saveToLocalStorage('panganin_orders', orders);
    renderOrders();
    showToast(`Status ${order.orderNumber} diubah ke: ${ORDER_STATUS_STEPS[order.statusIndex].name}`, 'info');
  }

  // Mengubah status pesanan secara spesifik (Demo)
  function setOrderStatus(orderId, targetIndex) {
    const order = orders.find(o => o.id === orderId);
    if (!order) return;

    order.statusIndex = targetIndex;
    saveToLocalStorage('panganin_orders', orders);
    renderOrders();
    showToast(`Status ${order.orderNumber} diubah ke: ${ORDER_STATUS_STEPS[order.statusIndex].name}`, 'info');
  }

  // Menghapus riwayat pesanan tertentu (Demo)
  function deleteOrder(orderId) {
    if (!confirm('Hapus riwayat pesanan ini?')) return;
    orders = orders.filter(o => o.id !== orderId);
    saveToLocalStorage('panganin_orders', orders);
    renderOrders();
    showToast('Pesanan telah dihapus dari daftar.', 'info');
  }

  // Render Daftar Status Pesanan
  function renderOrders() {
    if (!ordersEmptyState || !ordersListContainer) return;

    if (orders.length === 0) {
      ordersEmptyState.classList.remove('hidden');
      ordersListContainer.classList.add('hidden');
      return;
    }

    ordersEmptyState.classList.add('hidden');
    ordersListContainer.classList.remove('hidden');
    ordersListContainer.innerHTML = '';

    orders.forEach(order => {
      const orderCard = document.createElement('article');
      orderCard.className = 'order-card';

      // Buat Stepper HTML
      let stepperHtml = '<ol class="order-stepper">';
      ORDER_STATUS_STEPS.forEach((step, idx) => {
        let stepStateClass = 'pending';
        let indicatorContent = idx + 1;
        let badgeHtml = `<span class="step-badge badge-pending">Menunggu</span>`;

        if (idx < order.statusIndex) {
          stepStateClass = 'completed';
          indicatorContent = '✓';
          badgeHtml = `<span class="step-badge badge-success">Selesai</span>`;
        } else if (idx === order.statusIndex) {
          stepStateClass = 'current';
          badgeHtml = `<span class="step-badge badge-primary">Aktif</span>`;
        }

        stepperHtml += `
          <li class="stepper-step ${stepStateClass}">
            <div class="step-indicator">${indicatorContent}</div>
            <div class="step-details">
              <h4 class="step-title">${step.name}</h4>
              <p class="step-desc">${step.desc}</p>
              ${badgeHtml}
            </div>
          </li>
        `;
      });
      stepperHtml += '</ol>';

      // Rincian Item Pesanan
      let itemsListHtml = '';
      order.items.forEach(item => {
        itemsListHtml += `
          <div class="order-mini-item">
            <span class="order-mini-item-name">${item.name} <strong>x${item.qty}</strong></span>
            <span class="order-mini-item-price">${formatRupiah(item.price * item.qty)}</span>
          </div>
        `;
      });

      // Tombol Kontrol Demo Status
      let demoButtonsHtml = `
        <div class="demo-control-panel">
          <div class="demo-control-info">
            <span>🕹️ Simulasi Status (Demo):</span>
          </div>
          <div class="demo-control-actions">
            ${ORDER_STATUS_STEPS.map((step, idx) => `
              <button 
                type="button" 
                class="demo-step-btn ${idx === order.statusIndex ? 'active' : ''}" 
                data-order-id="${order.id}" 
                data-step-index="${idx}">
                ${step.badgeText}
              </button>
            `).join('')}
            <button 
              type="button" 
              class="btn-next-status" 
              data-order-id="${order.id}">
              Lanjut Status ⏩
            </button>
            <button 
              type="button" 
              class="btn-text-danger" 
              data-order-delete-id="${order.id}"
              title="Hapus pesanan ini">
              Hapus
            </button>
          </div>
        </div>
      `;

      orderCard.innerHTML = `
        <div class="order-card-header">
          <div>
            <div class="order-meta-title">
              <span class="order-number">${order.orderNumber}</span>
              <span class="order-customer-chip">📍 ${order.student.location}</span>
            </div>
            <p class="order-timestamp">Dipesan pada ${order.createdAt} • Pemesan: <strong>${order.student.name}</strong> (NIM: ${order.student.nim})</p>
          </div>
          <div>
            <span class="total-label">Total: </span>
            <span class="total-price">${formatRupiah(order.totalPrice)}</span>
          </div>
        </div>

        ${stepperHtml}

        <div class="order-details-box">
          <h5 class="order-details-title">Detail Hidangan:</h5>
          <div class="order-items-mini-list">
            ${itemsListHtml}
          </div>
          ${order.student.notes ? `<div class="order-notes-tag">📝 Catatan: "${order.student.notes}"</div>` : ''}
        </div>

        ${demoButtonsHtml}
      `;

      ordersListContainer.appendChild(orderCard);
    });

    // Event Listener untuk Tombol Kontrol Demo Status
    ordersListContainer.querySelectorAll('.demo-step-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const orderId = Number(e.currentTarget.getAttribute('data-order-id'));
        const stepIndex = Number(e.currentTarget.getAttribute('data-step-index'));
        setOrderStatus(orderId, stepIndex);
      });
    });

    ordersListContainer.querySelectorAll('.btn-next-status').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const orderId = Number(e.currentTarget.getAttribute('data-order-id'));
        advanceOrderStatus(orderId);
      });
    });

    ordersListContainer.querySelectorAll('[data-order-delete-id]').forEach(btn => {
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
  renderOrders();

  console.log('✅ Panganin App berhasil diinisialisasi dengan data localStorage.');
});
