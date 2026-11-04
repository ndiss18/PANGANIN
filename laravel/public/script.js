document.addEventListener('DOMContentLoaded', () => {
  const body = document.body;
  const menuApiUrl = body.dataset.menuApiUrl;
  const cartApiUrl = body.dataset.cartApiUrl;
  const orderApiUrl = body.dataset.orderApiUrl;
  const orderUrlPrefix = body.dataset.orderUrlPrefix;
  const sessionApiUrl = body.dataset.sessionApiUrl;
  const menuUrlPrefix = body.dataset.menuUrlPrefix;
  const menuGrid = document.getElementById('menu-grid');
  const filterButtons = document.querySelectorAll('.filter-btn');
  const searchInput = document.getElementById('menu-search');
  const checkoutForm = document.getElementById('checkout-form');
  const errorBox = document.getElementById('checkout-error');
  const cartTableBody = document.getElementById('cart-table-body');
  const orderHistoryEmpty = document.getElementById('order-history-empty');
  const orderHistoryList = document.getElementById('order-history-list');
  const categoriesByFilter = {
    makanan: ['Makanan berat', 'Mie dan bakso', 'Lauk dan gorengan'],
    minuman: ['Minuman'],
    camilan: ['Camilan']
  };
  let menuData = [];
  let cart = { items: [], total_qty: 0, total_price: 0 };
  let currentUser = null;
  let csrfToken = '';
  let currentCategory = 'all';
  let currentSearch = '';

  function formatRupiah(value) {
    return `Rp${Number(value).toLocaleString('id-ID')}`;
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

  async function request(url, options = {}) {
    const headers = new Headers(options.headers || {});
    headers.set('Accept', 'application/json');
    if (options.body) headers.set('Content-Type', 'application/json');
    if (csrfToken && options.method && options.method !== 'GET') {
      headers.set('X-CSRF-TOKEN', csrfToken);
    }

    const response = await fetch(url, {
      credentials: 'same-origin',
      ...options,
      headers
    });
    const result = await response.json().catch(() => null);
    if (!response.ok) {
      const validationMessage = result?.errors
        ? Object.values(result.errors).flat()[0]
        : null;
      throw new Error(validationMessage || result?.message || result?.error || `Permintaan gagal (HTTP ${response.status}).`);
    }
    return result;
  }

  function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    const messageElement = document.createElement('span');
    messageElement.textContent = message;
    toast.appendChild(messageElement);
    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 300);
    }, 3200);
  }

  function showCheckoutError(message) {
    if (!errorBox) return;
    errorBox.textContent = message;
    errorBox.classList.remove('hidden');
    errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function hideCheckoutError() {
    if (!errorBox) return;
    errorBox.textContent = '';
    errorBox.classList.add('hidden');
  }

  function renderMenu() {
    if (!menuGrid) return;
    menuGrid.replaceChildren();
    const categories = categoriesByFilter[currentCategory];
    const searchTerm = currentSearch.toLocaleLowerCase('id-ID');
    const filteredMenus = menuData.filter(menu => {
      const matchesCategory = currentCategory === 'all' || categories.includes(menu.category);
      const searchableText = `${menu.name} ${menu.category} ${menu.description}`.toLocaleLowerCase('id-ID');
      return matchesCategory && searchableText.includes(searchTerm);
    });

    filteredMenus.forEach(menu => {
      const card = document.createElement('article');
      card.className = 'menu-card';
      const badgeClass = menu.category === 'Minuman'
        ? 'beverage'
        : menu.category === 'Camilan' || menu.category === 'Lauk dan gorengan'
          ? 'snack'
          : '';
      card.innerHTML = `
        <div class="card-media">
          <img src="${escapeHtml(menu.image)}" alt="${escapeHtml(menu.name)}" class="card-img" loading="lazy">
          <span class="category-badge ${badgeClass}">${escapeHtml(menu.category)}</span>
        </div>
        <div class="card-body">
          <h3 class="card-title"><a href="${menuUrlPrefix}/${encodeURIComponent(menu.id)}">${escapeHtml(menu.name)}</a></h3>
          <p class="card-desc">${escapeHtml(menu.description)}</p>
          <div class="card-footer">
            <span class="price-tag">${formatRupiah(menu.price)}</span>
            <button type="button" class="btn btn-sm btn-primary btn-add-menu" data-id="${menu.id}">Tambah</button>
          </div>
        </div>`;
      menuGrid.appendChild(card);
    });
  }

  function renderCart() {
    const emptyState = document.getElementById('cart-empty-state');
    const itemsContainer = document.getElementById('cart-items-container');
    const clearButton = document.getElementById('btn-clear-cart');
    const badge = document.getElementById('nav-cart-badge');
    const totalItems = document.getElementById('summary-total-items');
    const totalPrice = document.getElementById('summary-total-price');

    if (badge) badge.textContent = cart.total_qty;
    if (totalItems) totalItems.textContent = `${cart.total_qty} item`;
    if (totalPrice) totalPrice.textContent = formatRupiah(cart.total_price);

    if (!cart.items.length) {
      emptyState?.classList.remove('hidden');
      itemsContainer?.classList.add('hidden');
      if (clearButton) clearButton.style.visibility = 'hidden';
      if (cartTableBody) cartTableBody.replaceChildren();
      return;
    }

    emptyState?.classList.add('hidden');
    itemsContainer?.classList.remove('hidden');
    if (clearButton) clearButton.style.visibility = 'visible';
    if (!cartTableBody) return;

    cartTableBody.innerHTML = cart.items.map(item => `
      <tr class="cart-item-row">
        <td><div class="cart-item-info"><img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name)}" class="cart-thumb"><span class="item-name">${escapeHtml(item.name)}</span></div></td>
        <td class="unit-price">${formatRupiah(item.price)}</td>
        <td class="text-center"><div class="quantity-control">
          <button type="button" class="qty-btn btn-decrease" data-id="${item.id}" title="Kurangi">−</button>
          <span class="qty-number">${item.qty}</span>
          <button type="button" class="qty-btn btn-increase" data-id="${item.id}" title="Tambah">+</button>
        </div></td>
        <td class="text-right subtotal-price">${formatRupiah(item.subtotal)}</td>
        <td class="text-center"><button type="button" class="btn-delete-item" data-id="${item.id}" title="Hapus makanan ini">
          <svg aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M10 11v6m4-6v6M6 7l1 13h10l1-13M9 7V4h6v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button></td>
      </tr>`).join('');
  }

  function renderOrderHistory(orders) {
    if (!orderHistoryEmpty || !orderHistoryList) return;
    if (!orders.length) {
      orderHistoryEmpty.classList.remove('hidden');
      orderHistoryList.classList.add('hidden');
      return;
    }

    orderHistoryEmpty.classList.add('hidden');
    orderHistoryList.classList.remove('hidden');
    orderHistoryList.innerHTML = orders.map(order => {
      const items = order.items.map(item => `
        <div class="order-mini-item">
          <span class="order-mini-item-name">${escapeHtml(item.name)} <strong>x${item.qty}</strong></span>
          <span class="order-mini-item-price">${formatRupiah(item.price * item.qty)}</span>
        </div>`).join('');
      const notes = order.student.notes
        ? `<div class="order-notes-tag"><span>Catatan: "${escapeHtml(order.student.notes)}"</span></div>`
        : '';
      return `
        <article class="order-history-card">
          <div class="order-card-header">
            <div>
              <div class="order-meta-title"><span class="order-number">${escapeHtml(order.orderNumber)}</span></div>
              <p class="order-timestamp">Dipesan pada ${escapeHtml(order.createdAt)} · Pemesan: <strong>${escapeHtml(order.student.name)}</strong></p>
              <p class="order-location">Lokasi pengambilan: ${escapeHtml(order.student.location)}</p>
              <p class="order-location">Status: ${escapeHtml(order.status)}</p>
            </div>
            <div><span class="total-label">Total Pembayaran: </span><span class="total-price">${formatRupiah(order.totalPrice)}</span></div>
          </div>
          <div class="order-details-box"><h5 class="order-details-title">Detail Pesanan:</h5><div class="order-items-mini-list">${items}</div>${notes}</div>
          <a class="btn btn-secondary btn-sm" href="${orderUrlPrefix}/${encodeURIComponent(order.id)}">Lihat rincian</a>
        </article>`;
    }).join('');
  }

  async function loadCart() {
    try {
      cart = await request(cartApiUrl);
      renderCart();
    } catch (error) {
      if (error.message.includes('tidak tersedia')) {
        const clearButton = document.getElementById('btn-clear-cart');
        if (clearButton) clearButton.style.visibility = 'visible';
        showCheckoutError(error.message);
      }
      throw error;
    }
  }

  async function loadOrderHistory() {
    if (!currentUser) {
      renderOrderHistory([]);
      return;
    }
    const orders = await request(orderApiUrl);
    renderOrderHistory(orders);
  }

  async function loadSession() {
    const session = await request(sessionApiUrl);
    currentUser = session.user;
    csrfToken = session.csrf_token;

    const status = document.getElementById('account-status');
    const loginLink = document.getElementById('login-link');
    const registerLink = document.getElementById('register-link');
    const logoutForm = document.getElementById('logout-form');
    const logoutToken = document.getElementById('logout-csrf-token');
    const managementLink = document.getElementById('management-link');
    const nameInput = document.getElementById('student-name');

    if (currentUser) {
      if (status) status.textContent = `Masuk sebagai ${currentUser.nama} (${currentUser.role}).`;
      loginLink?.classList.add('hidden');
      registerLink?.classList.add('hidden');
      logoutForm?.classList.remove('hidden');
      if (logoutToken) logoutToken.value = csrfToken;
      if (managementLink && currentUser.role === 'admin') managementLink.classList.remove('hidden');
      if (nameInput) {
        nameInput.value = currentUser.nama;
        nameInput.readOnly = true;
      }
    } else {
      if (status) status.textContent = 'Masuk atau daftar untuk menyimpan pesanan ke akunmu.';
      logoutForm?.classList.add('hidden');
      managementLink?.classList.add('hidden');
      if (nameInput) nameInput.readOnly = false;
    }
    await loadOrderHistory();
  }

  filterButtons.forEach(button => {
    button.addEventListener('click', () => {
      filterButtons.forEach(filter => filter.classList.remove('active'));
      button.classList.add('active');
      currentCategory = button.dataset.category || 'all';
      renderMenu();
    });
  });

  searchInput?.addEventListener('input', () => {
    currentSearch = searchInput.value.trim();
    renderMenu();
  });

  menuGrid?.addEventListener('click', async event => {
    const button = event.target.closest('.btn-add-menu');
    if (!button || !menuGrid.contains(button)) return;
    try {
      cart = await request(`${cartApiUrl}/${button.dataset.id}`, { method: 'POST' });
      renderCart();
      showToast('Menu berhasil masuk ke keranjang.', 'success');
    } catch (error) {
      showToast(error.message, 'danger');
    }
  });

  cartTableBody?.addEventListener('click', async event => {
    const button = event.target.closest('[data-id]');
    if (!button) return;
    const item = cart.items.find(cartItem => cartItem.id === Number(button.dataset.id));
    if (!item) return;
    try {
      if (button.classList.contains('btn-delete-item')) {
        cart = await request(`${cartApiUrl}/${item.id}`, { method: 'DELETE' });
      } else if (button.classList.contains('btn-decrease') && item.qty === 1) {
        cart = await request(`${cartApiUrl}/${item.id}`, { method: 'DELETE' });
      } else {
        const quantity = item.qty + (button.classList.contains('btn-increase') ? 1 : -1);
        cart = await request(`${cartApiUrl}/${item.id}`, {
          method: 'PUT',
          body: JSON.stringify({ quantity })
        });
      }
      renderCart();
    } catch (error) {
      showToast(error.message, 'danger');
    }
  });

  document.getElementById('btn-clear-cart')?.addEventListener('click', async () => {
    if (!confirm('Apakah kamu yakin ingin mengosongkan keranjang?')) return;
    try {
      cart = await request(cartApiUrl, { method: 'DELETE' });
      renderCart();
    } catch (error) {
      showToast(error.message, 'danger');
    }
  });

  checkoutForm?.addEventListener('submit', async event => {
    event.preventDefault();
    hideCheckoutError();
    if (!currentUser) {
      showCheckoutError('Silakan masuk atau daftar terlebih dahulu sebelum membuat pesanan.');
      return;
    }
    if (!cart.items.length) {
      showCheckoutError('Keranjangmu masih kosong. Silakan tambahkan menu sebelum memesan.');
      return;
    }

    const submitButton = document.getElementById('btn-submit-order');
    const inputName = document.getElementById('student-name');
    const location = document.getElementById('pickup-location');
    const notes = document.getElementById('order-notes');
    if (submitButton) submitButton.disabled = true;

    try {
      const order = await request(orderApiUrl, {
        method: 'POST',
        body: JSON.stringify({
          name: inputName?.value.trim(),
          pickup_location: location?.value.trim(),
          notes: notes?.value.trim()
        })
      });
      await loadCart();
      checkoutForm.reset();
      if (inputName && currentUser) {
        inputName.value = currentUser.nama;
        inputName.readOnly = true;
      }
      await loadOrderHistory();
      showToast(`Pesanan #${order.nomor_pesanan} berhasil dibuat.`, 'success');
      document.getElementById('riwayat-pesanan')?.scrollIntoView({ behavior: 'smooth' });
    } catch (error) {
      showCheckoutError(error.message);
    } finally {
      if (submitButton) submitButton.disabled = false;
    }
  });

  document.addEventListener('panganin:menu-loaded', event => {
    menuData = event.detail;
    renderMenu();
  });

  document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
      document.querySelectorAll('.nav-link').forEach(navLink => navLink.classList.remove('active'));
      link.classList.add('active');
    });
  });

  renderCart();
  Promise.all([loadCart(), loadSession()]).catch(error => {
    console.error('Gagal memuat data sesi Panganin:', error);
    const status = document.getElementById('account-status');
    if (status) status.textContent = 'Sesi akun atau keranjang tidak dapat dimuat. Muat ulang halaman.';
    showToast(error.message, 'danger');
  });
});
