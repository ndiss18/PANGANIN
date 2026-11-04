<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'menuCount' => Menu::count(),
            'orderCount' => Order::count(),
            'pendingCount' => Order::where('status', 'pending')->count(),
        ]);
    }

    public function menus(Request $request): View
    {
        $editingMenu = $request->filled('edit') ? Menu::findOrFail($request->integer('edit')) : null;

        return view('admin.menus', [
            'menus' => Menu::query()->orderBy('nama_menu')->get(),
            'editingMenu' => $editingMenu,
            'categories' => Menu::query()->distinct()->orderBy('kategori')->pluck('kategori'),
        ]);
    }

    public function storeMenu(Request $request): RedirectResponse
    {
        Menu::create($this->validatedMenu($request));

        return redirect()->route('admin.menus')->with('status', 'Menu berhasil ditambahkan.');
    }

    public function updateMenu(Request $request, Menu $menu): RedirectResponse
    {
        $menu->update($this->validatedMenu($request));

        return redirect()->route('admin.menus')->with('status', 'Menu berhasil diperbarui.');
    }

    public function deleteMenu(Menu $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()->route('admin.menus')->with('status', 'Menu berhasil dihapus.');
    }

    public function orders(): View
    {
        return view('admin.orders', [
            'orders' => Order::query()->with('details')->latest()->get(),
            'statuses' => ['pending', 'diproses', 'siap_diambil', 'selesai', 'dibatalkan'],
        ]);
    }

    public function updateOrderStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'diproses', 'siap_diambil', 'selesai', 'dibatalkan'])],
        ]);
        $order->update($validated);

        return redirect()->route('admin.orders')->with('status', 'Status pesanan berhasil diperbarui.');
    }

    private function validatedMenu(Request $request): array
    {
        return $request->validate([
            'nama_menu' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'string', 'max:100'],
            'harga' => ['required', 'integer', 'min:1', 'max:999999999999'],
            'gambar' => ['required', 'url:https', 'max:2048'],
            'deskripsi' => ['required', 'string', 'max:2000'],
        ]);
    }
}
