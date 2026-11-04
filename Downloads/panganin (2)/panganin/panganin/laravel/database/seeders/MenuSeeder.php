<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['Nasi goreng', 'Makanan berat', 15000, 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=600&q=80', 'Nasi goreng berbumbu gurih dengan telur, suwiran ayam, dan kerupuk.'],
            ['Ayam geprek', 'Makanan berat', 18000, 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=600&q=80', 'Ayam goreng dengan sambal geprek dan nasi hangat.'],
            ['Nasi telur', 'Makanan berat', 12000, 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=600&q=80', 'Nasi hangat dengan telur dan lauk sederhana.'],
            ['Nasi pecel', 'Makanan berat', 13000, 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=600&q=80', 'Nasi dengan sayuran rebus dan siraman bumbu kacang.'],
            ['Mie ayam', 'Mie dan bakso', 13000, 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=600&q=80', 'Mie dengan ayam kecap, sawi hijau, dan kuah kaldu.'],
            ['Bakso', 'Mie dan bakso', 14000, 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=600&q=80', 'Bakso dengan kuah hangat, mie, dan sayuran.'],
            ['Nasi rames', 'Makanan berat', 15000, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80', 'Nasi dengan pilihan lauk dan sayur rumahan.'],
            ['Soto ayam', 'Makanan berat', 15000, 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=600&q=80', 'Soto ayam berkuah gurih dengan nasi dan pelengkap.'],
            ['Es teh', 'Minuman', 5000, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=600&q=80', 'Seduhan teh melati dengan es batu yang menyegarkan.'],
            ['Es jeruk', 'Minuman', 7000, 'https://images.unsplash.com/photo-1547514701-42782101795e?auto=format&fit=crop&w=600&q=80', 'Minuman jeruk segar yang disajikan dengan es.'],
        ];

        foreach ($menus as [$name, $category, $price, $image, $description]) {
            Menu::updateOrCreate(
                ['nama_menu' => $name],
                [
                    'kategori' => $category,
                    'harga' => $price,
                    'gambar' => $image,
                    'deskripsi' => $description,
                ]
            );
        }
    }
}
