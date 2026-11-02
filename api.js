
const MENU_API_URL = './api/products.php';

document.addEventListener('DOMContentLoaded', () => {
  const statusElement = document.getElementById('menu-status');
  const retryButton = document.getElementById('menu-retry-button');

  if (!statusElement || !retryButton) {
    console.error('Elemen status katalog tidak ditemukan di halaman.');
    return;
  }

  function setStatus(message, state) {
    statusElement.textContent = message;
    statusElement.setAttribute('role', state === 'error' ? 'alert' : 'status');
    statusElement.setAttribute('aria-busy', state === 'loading' ? 'true' : 'false');
    statusElement.classList.toggle('hidden', state === 'success');
    retryButton.classList.toggle('hidden', state !== 'error');
    retryButton.disabled = state === 'loading';
  }

  function isValidMenuItem(item, ids) {
    if (
      !item ||
      !Number.isSafeInteger(item.id) ||
      item.id <= 0 ||
      typeof item.name !== 'string' ||
      !item.name.trim() ||
      !Number.isSafeInteger(item.price) ||
      item.price <= 0 ||
      typeof item.category !== 'string' ||
      !item.category.trim() ||
      typeof item.description !== 'string' ||
      !item.description.trim() ||
      typeof item.image !== 'string'
    ) {
      return false;
    }

    if (ids.has(item.id)) return false;

    try {
      const imageUrl = new URL(item.image);
      if (imageUrl.protocol !== 'https:') return false;
      ids.add(item.id);
      return true;
    } catch (error) {
      return false;
    }
  }

  async function loadMenu() {
    setStatus('Memuat katalog makanan...', 'loading');

    try {
      const response = await fetch(MENU_API_URL, {
        method: 'GET',
        credentials: 'same-origin'
      });

      if (!response.ok) {
        throw new Error(
          `Permintaan katalog gagal dengan status HTTP ${response.status}.`
        );
      }

      let menuItems;

      try {
        menuItems = await response.json();
      } catch {
        throw new Error('Respons server bukan JSON yang valid.');
      }

      const ids = new Set();

      if (
        !Array.isArray(menuItems) ||
        menuItems.some(item => !isValidMenuItem(item, ids))
      ) {
        throw new Error('Format data menu kantin tidak valid.');
      }

      if (menuItems.length === 0) {
        setStatus('Belum ada menu yang dapat ditampilkan.', 'empty');

        document.dispatchEvent(
          new CustomEvent('panganin:menu-loaded', {
            detail: menuItems
          })
        );

        return;
      }

      setStatus('', 'success');

      document.dispatchEvent(
        new CustomEvent('panganin:menu-loaded', {
          detail: menuItems
        })
      );
    } catch (error) {
      console.error('Gagal memuat katalog menu kantin:', error);

      setStatus(
        'Katalog menu gagal dimuat. Periksa koneksi, lalu coba lagi.',
        'error'
      );
    }
  }

  retryButton.addEventListener('click', loadMenu);
  loadMenu();
});
