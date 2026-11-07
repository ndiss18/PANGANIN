<?php
declare(strict_types=1);

final class DashboardController extends Controller
{
    public function index(): void
    {
        $user = $this->requireRole(['admin', 'umkm']);
        if ($user === null) {
            return;
        }

        try {
            $models = [new Product(), new Category(), new Order()];
            $counts = array_map(
                static fn(Model $model): int => $model->countRecords(),
                $models
            );
            $this->view('dashboard/index', [
                'user' => $user,
                'counts' => [
                    'products' => $counts[0],
                    'categories' => $counts[1],
                    'orders' => $counts[2],
                ],
                'csrfToken' => Session::csrfToken(),
            ]);
        } catch (PDOException $exception) {
            error_log('Gagal memuat dashboard Panganin: ' . $exception->getMessage());
            http_response_code(500);
            $this->view('dashboard/index', [
                'user' => $user,
                'counts' => null,
                'error' => 'Dashboard tidak dapat dimuat. Periksa koneksi database.',
                'csrfToken' => Session::csrfToken(),
            ]);
        }
    }
}
