<?php
declare(strict_types=1);

final class OrderController extends Controller
{
    public function history(): void
    {
        $user = $this->requireRole(['customer', 'admin', 'umkm'], true);
        if ($user === null) {
            return;
        }
        try {
            $this->json((new Order())->historyForUser((int) $user['id']));
        } catch (PDOException $exception) {
            error_log('Gagal mengambil riwayat pesanan Panganin: ' . $exception->getMessage());
            $this->json(['error' => 'Riwayat pesanan tidak dapat dimuat.'], 500);
        }
    }

    public function store(): void
    {
        $user = $this->requireRole(['customer', 'admin', 'umkm'], true);
        if ($user === null) {
            return;
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Allow: POST');
            $this->json(['error' => 'Metode permintaan tidak diizinkan.'], 405);
            return;
        }

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Session::verifyCsrf(is_string($token) ? $token : null)) {
            $this->json(['error' => 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba kembali.'], 419);
            return;
        }

        try {
            $input = json_decode((string) file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->json(['error' => 'Format data JSON tidak valid.'], 400);
            return;
        }
        if (!is_array($input)) {
            $this->json(['error' => 'Data permintaan harus berupa objek JSON.'], 400);
            return;
        }

        $items = $input['items'] ?? null;
        $location = $input['pickup_location'] ?? null;
        $notes = $input['notes'] ?? '';
        if (!is_array($items) || count($items) < 1 || count($items) > 50) {
            $this->json(['error' => 'Keranjang harus berisi antara 1 dan 50 jenis menu.'], 422);
            return;
        }
        if (!is_string($location) || trim($location) === '' || strlen(trim($location)) > 160) {
            $this->json(['error' => 'Lokasi pengambilan wajib diisi dan maksimal 160 karakter.'], 422);
            return;
        }
        if (!is_string($notes) || strlen($notes) > 1000) {
            $this->json(['error' => 'Catatan maksimal 1000 karakter.'], 422);
            return;
        }

        $quantities = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                $this->json(['error' => 'Format item keranjang tidak valid.'], 422);
                return;
            }
            $rawProductId = $item['product_id'] ?? null;
            $rawQuantity = $item['quantity'] ?? null;
            $productId = is_string($rawProductId) || is_int($rawProductId)
                ? filter_var($rawProductId, FILTER_VALIDATE_INT)
                : false;
            $quantity = is_string($rawQuantity) || is_int($rawQuantity)
                ? filter_var($rawQuantity, FILTER_VALIDATE_INT)
                : false;
            if ($productId === false || $productId < 1 || $quantity === false || $quantity < 1 || $quantity > 100) {
                $this->json(['error' => 'ID menu atau jumlah pesanan tidak valid.'], 422);
                return;
            }
            $quantities[$productId] = ($quantities[$productId] ?? 0) + $quantity;
            if ($quantities[$productId] > 100) {
                $this->json(['error' => 'Jumlah per menu tidak boleh lebih dari 100.'], 422);
                return;
            }
        }

        try {
            $order = (new Order())->createForCustomer(
                (int) $user['id'],
                $quantities,
                trim($location),
                trim($notes) === '' ? null : trim($notes)
            );
            $this->json($order, 201);
        } catch (DomainException $exception) {
            $this->json(['error' => $exception->getMessage()], 409);
        } catch (PDOException $exception) {
            error_log('Gagal menyimpan pesanan Panganin: ' . $exception->getMessage());
            $this->json(['error' => 'Pesanan gagal disimpan. Periksa koneksi database lalu coba lagi.'], 500);
        }
    }
}
