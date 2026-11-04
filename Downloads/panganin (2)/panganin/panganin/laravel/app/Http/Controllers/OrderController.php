<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function history(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with('details')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($orders->map(fn (Order $order): array => $this->toApiOrder($order)));
    }

    public function show(Request $request, Order $order): View|JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id || $request->user()->role === 'admin', 403);
        $order->load('details');

        if ($request->expectsJson()) {
            return response()->json($this->toApiOrder($order));
        }

        return view('orders.show', compact('order'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'pickup_location' => ['required', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $cart = $request->session()->get('cart', []);

        if ($cart === []) {
            return response()->json(['message' => 'Keranjang masih kosong.'], 422);
        }

        $order = DB::transaction(function () use ($cart, $request, $validated): Order {
            $menus = Menu::query()->whereKey(array_keys($cart))->lockForUpdate()->get()->keyBy('id');
            if ($menus->count() !== count($cart)) {
                abort(409, 'Salah satu menu sudah tidak tersedia. Perbarui keranjang sebelum checkout.');
            }

            $details = [];
            $total = 0;
            foreach ($cart as $menuId => $quantity) {
                $menu = $menus->get($menuId);
                $quantity = (int) $quantity;
                abort_if($quantity < 1 || $quantity > 100, 422, 'Jumlah menu di keranjang tidak valid.');
                $subtotal = $menu->harga * $quantity;
                $total += $subtotal;
                $details[] = [
                    'menu_id' => $menu->id,
                    'nama_menu' => $menu->nama_menu,
                    'harga' => $menu->harga,
                    'jumlah' => $quantity,
                    'subtotal' => $subtotal,
                ];
            }

            $order = Order::create([
                'nomor_pesanan' => 'PGN-'.Str::upper(Str::random(8)),
                'user_id' => $request->user()->id,
                'nama_pelanggan' => $validated['name'],
                'lokasi_pengambilan' => $validated['pickup_location'],
                'catatan' => $validated['notes'] ?? null,
                'total_harga' => $total,
                'status' => 'pending',
            ]);
            $order->details()->createMany($details);

            return $order->load('details');
        });

        $request->session()->forget('cart');

        return response()->json([
            'id' => $order->id,
            'nomor_pesanan' => $order->nomor_pesanan,
            'total_harga' => $order->total_harga,
            'status' => $order->status,
        ], 201);
    }

    private function toApiOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'orderNumber' => '#'.$order->nomor_pesanan,
            'createdAt' => $order->created_at->format('d M Y, H:i').' WIB',
            'student' => [
                'name' => $order->nama_pelanggan,
                'location' => $order->lokasi_pengambilan,
                'notes' => $order->catatan ?? '',
            ],
            'items' => $order->details->map(fn ($detail): array => [
                'name' => $detail->nama_menu,
                'qty' => $detail->jumlah,
                'price' => $detail->harga,
            ])->all(),
            'totalPrice' => $order->total_harga,
            'status' => $order->status,
        ];
    }
}
