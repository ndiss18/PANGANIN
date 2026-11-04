<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json($this->cartPayload($request));
    }

    public function add(Request $request, Menu $menu): JsonResponse
    {
        $cart = $request->session()->get('cart', []);
        $quantity = ($cart[$menu->id] ?? 0) + 1;
        abort_if($quantity > 100, 422, 'Jumlah per menu tidak boleh lebih dari 100.');
        $cart[$menu->id] = $quantity;
        $request->session()->put('cart', $cart);

        return response()->json($this->cartPayload($request), 201);
    }

    public function update(Request $request, Menu $menu): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);
        $cart = $request->session()->get('cart', []);
        abort_unless(isset($cart[$menu->id]), 404, 'Menu tidak ditemukan di keranjang.');
        $cart[$menu->id] = (int) $validated['quantity'];
        $request->session()->put('cart', $cart);

        return response()->json($this->cartPayload($request));
    }

    public function remove(Request $request, Menu $menu): JsonResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$menu->id]);
        $request->session()->put('cart', $cart);

        return response()->json($this->cartPayload($request));
    }

    public function clear(Request $request): JsonResponse
    {
        $request->session()->forget('cart');

        return response()->json($this->cartPayload($request));
    }

    private function cartPayload(Request $request): array
    {
        $cart = $request->session()->get('cart', []);
        $menus = Menu::query()->whereKey(array_keys($cart))->get()->keyBy('id');
        abort_if($menus->count() !== count($cart), 409, 'Salah satu menu di keranjang sudah tidak tersedia. Kosongkan keranjang dan pilih menu yang tersedia.');

        $items = collect($cart)->map(function (int $quantity, int|string $id) use ($menus): array {
            /** @var Menu $menu */
            $menu = $menus->get($id);

            return [
                'id' => $menu->id,
                'name' => $menu->nama_menu,
                'price' => $menu->harga,
                'image' => $menu->gambar,
                'qty' => $quantity,
                'subtotal' => $menu->harga * $quantity,
            ];
        })->values();

        return [
            'items' => $items,
            'total_qty' => $items->sum('qty'),
            'total_price' => $items->sum('subtotal'),
        ];
    }
}
