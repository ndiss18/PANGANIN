<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        return view('menu.index');
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $menus = $this->filteredMenus($request)->get();

        return response()->json($menus->map(fn (Menu $menu): array => $this->toApiMenu($menu)));
    }

    public function categories(): JsonResponse
    {
        return response()->json(
            Menu::query()->distinct()->orderBy('kategori')->pluck('kategori')->values()
        );
    }

    public function show(Menu $menu): View
    {
        return view('menu.show', compact('menu'));
    }

    private function filteredMenus(Request $request): Builder
    {
        return Menu::query()
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $term = '%'.$request->string('q')->trim().'%';
                $query->where(function (Builder $search) use ($term): void {
                    $search->where('nama_menu', 'like', $term)
                        ->orWhere('deskripsi', 'like', $term)
                        ->orWhere('kategori', 'like', $term);
                });
            })
            ->when($request->filled('category'), fn (Builder $query) => $query->where('kategori', (string) $request->input('category')))
            ->orderBy('nama_menu');
    }

    private function toApiMenu(Menu $menu): array
    {
        return [
            'id' => $menu->id,
            'name' => $menu->nama_menu,
            'category' => $menu->kategori,
            'price' => $menu->harga,
            'image' => $menu->gambar,
            'description' => $menu->deskripsi,
        ];
    }
}
