<?php
declare(strict_types=1);

final class ProductController extends Controller
{
    public function index(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            header('Allow: GET');
            $this->json(['error' => 'Metode permintaan tidak diizinkan.'], 405);
            return;
        }

        $search = $_GET['q'] ?? null;
        $rawCategory = $_GET['category_id'] ?? null;
        $categoryId = $rawCategory === null || $rawCategory === ''
            ? null
            : (is_string($rawCategory) || is_int($rawCategory)
                ? filter_var($rawCategory, FILTER_VALIDATE_INT)
                : false);
        if ($categoryId === false || ($categoryId !== null && $categoryId < 1)) {
            $this->json(['error' => 'Kategori tidak valid.'], 422);
            return;
        }

        try {
            $rows = (new Product())->all(is_string($search) ? $search : null, $categoryId);
            $products = array_map(
                static fn(array $product): array => [
                    'id' => (int) $product['id'],
                    'name' => $product['nama_produk'],
                    'price' => (int) $product['harga'],
                    'category' => $product['nama_kategori'],
                    'image' => $product['gambar'],
                    'description' => $product['deskripsi'],
                ],
                $rows
            );
            $this->json($products);
        } catch (PDOException $exception) {
            error_log('Gagal mengambil katalog Panganin: ' . $exception->getMessage());
            $this->json([
                'error' => 'Koneksi database gagal. Pastikan MySQL aktif di XAMPP, database panganin_db Week 5 sudah diimpor, dan pengaturan config/database.php sesuai.',
            ], 500);
        }
    }

    public function show(): void
    {
        $rawId = $_GET['id'] ?? null;
        $id = is_string($rawId) || is_int($rawId) ? filter_var($rawId, FILTER_VALIDATE_INT) : false;
        if ($id === false || $id === null || $id < 1) {
            http_response_code(404);
            $this->view('products/detail', ['product' => null]);
            return;
        }

        try {
            $product = (new Product())->find($id);
            if ($product === null) {
                http_response_code(404);
            }
            $this->view('products/detail', ['product' => $product]);
        } catch (PDOException $exception) {
            error_log('Gagal mengambil detail produk Panganin: ' . $exception->getMessage());
            http_response_code(500);
            $this->view('products/detail', ['product' => null, 'error' => 'Detail menu tidak dapat dimuat.']);
        }
    }

    public function manage(): void
    {
        $user = $this->requireRole(['admin', 'umkm']);
        if ($user === null) {
            return;
        }

        $error = '';
        $message = isset($_GET['saved']) ? 'Menu berhasil disimpan.' : '';
        $editingProduct = null;
        $categories = [];
        $products = [];

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $token = $_POST['csrf_token'] ?? null;
            if (!Session::verifyCsrf(is_string($token) ? $token : null)) {
                $error = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba kembali.';
            } elseif (($_POST['action'] ?? '') === 'delete') {
                $rawId = $_POST['id'] ?? null;
                $id = is_string($rawId) || is_int($rawId) ? filter_var($rawId, FILTER_VALIDATE_INT) : false;
                if ($id === false || $id < 1) {
                    $error = 'ID menu tidak valid.';
                } else {
                    try {
                        if (!(new Product())->delete($id)) {
                            $error = 'Menu tidak ditemukan.';
                        } else {
                            header('Location: ' . appUrl('products/manage') . '&saved=1');
                            exit;
                        }
                    } catch (PDOException $exception) {
                        if ($exception->getCode() === '23000') {
                            $error = 'Menu sudah digunakan dalam pesanan dan tidak dapat dihapus. Atur stok menjadi 0 jika menu tidak dijual lagi.';
                        } else {
                            error_log('Gagal menghapus produk Panganin: ' . $exception->getMessage());
                            $error = 'Menu tidak dapat dihapus. Periksa koneksi database.';
                        }
                    }
                }
            } elseif (($_POST['action'] ?? '') === 'save') {
                [$editingProduct, $error] = $this->validateProductInput();
                if ($error === '') {
                    try {
                        $categoryId = (int) $editingProduct['category_id'];
                        if (!(new Category())->exists($categoryId)) {
                            $error = 'Kategori yang dipilih tidak ditemukan.';
                        } else {
                            $id = $editingProduct['id'] === '' ? null : (int) $editingProduct['id'];
                            (new Product())->save($id, [
                                ':nama_produk' => $editingProduct['nama_produk'],
                                ':deskripsi' => $editingProduct['deskripsi'],
                                ':harga' => (int) $editingProduct['harga'],
                                ':gambar' => $editingProduct['gambar'],
                                ':category_id' => $categoryId,
                                ':stok' => (int) $editingProduct['stok'],
                            ]);
                            header('Location: ' . appUrl('products/manage') . '&saved=1');
                            exit;
                        }
                    } catch (PDOException $exception) {
                        error_log('Gagal menyimpan produk Panganin: ' . $exception->getMessage());
                        $error = 'Menu gagal disimpan. Periksa koneksi dan data database.';
                    } catch (DomainException $exception) {
                        $error = $exception->getMessage();
                    }
                }
            } else {
                $error = 'Aksi menu tidak dikenal.';
            }
        }

        try {
            $categoryModel = new Category();
            $productModel = new Product();
            $categories = $categoryModel->all();
            if ($editingProduct === null) {
                $rawEditId = $_GET['edit'] ?? null;
                $editId = is_string($rawEditId) || is_int($rawEditId)
                    ? filter_var($rawEditId, FILTER_VALIDATE_INT)
                    : false;
                if ($editId !== false && $editId !== null && $editId > 0) {
                    $editingProduct = $productModel->findForManagement($editId);
                }
            }
            $products = $productModel->all();
        } catch (PDOException $exception) {
            error_log('Gagal membuka manajemen produk Panganin: ' . $exception->getMessage());
            $error = 'Data menu tidak dapat dimuat. Pastikan database sudah diimpor.';
        }

        $this->view('products/manage', [
            'user' => $user,
            'error' => $error,
            'message' => $message,
            'editingProduct' => $editingProduct,
            'categories' => $categories,
            'products' => $products,
            'csrfToken' => Session::csrfToken(),
        ]);
    }

    private function validateProductInput(): array
    {
        $idInput = $_POST['id'] ?? '';
        $rawId = is_string($idInput) ? $idInput : '';
        $name = is_string($_POST['nama_produk'] ?? null) ? trim($_POST['nama_produk']) : '';
        $description = is_string($_POST['deskripsi'] ?? null) ? trim($_POST['deskripsi']) : '';
        $image = is_string($_POST['gambar'] ?? null) ? trim($_POST['gambar']) : '';
        $priceInput = is_string($_POST['harga'] ?? null) ? $_POST['harga'] : '';
        $categoryInput = is_string($_POST['category_id'] ?? null) ? $_POST['category_id'] : '';
        $stockInput = is_string($_POST['stok'] ?? null) ? $_POST['stok'] : '';
        $price = filter_var($priceInput, FILTER_VALIDATE_INT);
        $categoryId = filter_var($categoryInput, FILTER_VALIDATE_INT);
        $stock = filter_var($stockInput, FILTER_VALIDATE_INT);
        $id = !is_string($idInput)
            ? false
            : ($rawId === '' ? null : filter_var($rawId, FILTER_VALIDATE_INT));

        $product = [
            'id' => $id === null ? '' : (string) $id,
            'nama_produk' => $name,
            'deskripsi' => $description,
            'harga' => is_scalar($priceInput) ? (string) $priceInput : '',
            'gambar' => $image,
            'category_id' => is_scalar($categoryInput) ? (string) $categoryInput : '',
            'stok' => is_scalar($stockInput) ? (string) $stockInput : '',
        ];

        if ($id === false || ($id !== null && $id < 1)) {
            return [$product, 'ID menu tidak valid.'];
        }
        if (strlen($name) > 600 || preg_match('/\A.{1,150}\z/us', $name) !== 1 || preg_match('/[\x00-\x1F]/', $name)) {
            return [$product, 'Nama menu wajib diisi dan maksimal 150 karakter.'];
        }
        if ($description === '' || strlen($description) > 2000) {
            return [$product, 'Deskripsi wajib diisi dan maksimal 2000 karakter.'];
        }
        if ($price === false || $price < 1 || $price > 4294967295) {
            return [$product, 'Harga harus berupa bilangan bulat lebih besar dari 0.'];
        }
        if ($categoryId === false || $categoryId < 1) {
            return [$product, 'Pilih kategori yang valid.'];
        }
        if ($stock === false || $stock < 0 || $stock > 999999) {
            return [$product, 'Stok harus berupa bilangan bulat antara 0 sampai 999999.'];
        }
        if (strlen($image) > 2048 || filter_var($image, FILTER_VALIDATE_URL) === false || parse_url($image, PHP_URL_SCHEME) !== 'https') {
            return [$product, 'Gambar harus menggunakan URL HTTPS yang valid.'];
        }

        return [$product, ''];
    }
}
