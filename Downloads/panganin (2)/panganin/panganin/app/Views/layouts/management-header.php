<header class="site-header">
  <div class="container header-container">
    <a class="brand" href="<?= e(appUrl()) ?>"><span class="brand-name">Panganin</span></a>
    <nav class="site-nav" aria-label="Navigasi akun">
      <ul class="nav-list">
        <li class="nav-item"><a class="nav-link" href="<?= e(appUrl()) ?>#menu">Katalog</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(appUrl('dashboard')) ?>">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(appUrl('products/manage')) ?>">Kelola Menu</a></li>
        <li class="nav-item"><span class="nav-link"><?= e($user['nama']) ?> (<?= e($user['role']) ?>)</span></li>
        <li class="nav-item">
          <form action="<?= e(appUrl('logout')) ?>" method="post">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <button type="submit" class="btn-text-danger">Keluar</button>
          </form>
        </li>
      </ul>
    </nav>
  </div>
</header>
