<?php
declare(strict_types=1);

final class CategoryController extends Controller
{
    public function index(): void
    {
        try {
            $categories = array_map(
                static fn(array $category): array => [
                    'id' => (int) $category['id'],
                    'name' => $category['nama_kategori'],
                ],
                (new Category())->all()
            );
            $this->json($categories);
        } catch (PDOException $exception) {
            error_log('Gagal mengambil kategori Panganin: ' . $exception->getMessage());
            $this->json(['error' => 'Kategori tidak dapat dimuat. Pastikan database sudah diimpor.'], 500);
        }
    }

    public function store(): void
    {
        if ($this->requireRole(['admin'], true) === null) {
            return;
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Allow: POST');
            $this->json(['error' => 'Metode permintaan tidak diizinkan.'], 405);
            return;
        }
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Session::verifyCsrf(is_string($token) ? $token : null)) {
            $this->json(['error' => 'Sesi keamanan tidak valid.'], 419);
            return;
        }
        $name = $_POST['nama_kategori'] ?? '';
        $name = is_string($name) ? trim($name) : '';
        if ($name === '' || strlen($name) > 100) {
            $this->json(['error' => 'Nama kategori wajib diisi (maksimal 100 karakter).'], 422);
            return;
        }

        try {
            $id = (new Category())->create($name);
            $this->json(['id' => $id, 'name' => $name], 201);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $this->json(['error' => 'Kategori tersebut sudah tersedia.'], 409);
                return;
            }
            error_log('Gagal menyimpan kategori Panganin: ' . $exception->getMessage());
            $this->json(['error' => 'Kategori tidak dapat disimpan.'], 500);
        }
    }
}
