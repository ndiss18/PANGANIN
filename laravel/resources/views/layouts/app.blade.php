<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Panganin | Kantin Kampus Digital')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>
<body>
  <header class="site-header">
    <div class="container header-container">
      <a href="{{ route('home') }}" class="brand">
        <span class="brand-name">Panganin</span>
        <span class="brand-tagline">Pesan makanan tanpa antre</span>
      </a>
      <nav class="site-nav" aria-label="Navigasi akun">
        <ul class="nav-list">
          <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#menu">Menu</a></li>
          @auth
            @if (auth()->user()->role === 'admin')
              <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a></li>
              <li class="nav-item"><a class="nav-link" href="{{ route('admin.menus') }}">Kelola Menu</a></li>
              <li class="nav-item"><a class="nav-link" href="{{ route('admin.orders') }}">Pesanan</a></li>
            @endif
            <li class="nav-item">
              <form action="{{ route('logout') }}" method="post">
                @csrf
                <button type="submit" class="btn-text-danger">Keluar</button>
              </form>
            </li>
          @else
            <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Masuk</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('admin.login') }}">Login Admin</a></li>
          @endauth
        </ul>
      </nav>
    </div>
  </header>
  <main class="cart-section">
    <div class="container">
      @if (session('status'))
        <div class="alert-box" role="status">{{ session('status') }}</div>
      @endif
      @yield('content')
    </div>
  </main>
</body>
</html>
