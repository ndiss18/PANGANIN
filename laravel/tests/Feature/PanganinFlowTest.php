<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PanganinFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_and_categories_are_served_from_the_database(): void
    {
        Menu::create([
            'nama_menu' => 'Nasi goreng',
            'kategori' => 'Makanan berat',
            'harga' => 15000,
            'gambar' => 'https://example.test/nasi-goreng.jpg',
            'deskripsi' => 'Menu makan siang.',
        ]);
        $menu = Menu::firstOrFail();

        $this->get('/menu/'.$menu->id)->assertOk()->assertSee('Nasi goreng');

        $this->getJson('/api/products?q=nasi')
            ->assertOk()
            ->assertJsonPath('0.name', 'Nasi goreng')
            ->assertJsonPath('0.price', 15000);

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertExactJson(['Makanan berat']);
        $this->getJson('/api/products?category=Makanan%20berat')
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_session_cart_checkout_uses_current_database_prices_and_persists_order_details(): void
    {
        $user = $this->makeUser();
        $menu = Menu::create([
            'nama_menu' => 'Nasi goreng',
            'kategori' => 'Makanan berat',
            'harga' => 15000,
            'gambar' => 'https://example.test/nasi-goreng.jpg',
            'deskripsi' => 'Menu makan siang.',
        ]);

        $this->actingAs($user)
            ->withSession(['cart' => [$menu->id => 2]])
            ->postJson('/api/orders', [
                'name' => 'Mahasiswa',
                'pickup_location' => 'Meja 14',
                'notes' => 'Sambal dipisah',
                'items' => [['menu_id' => $menu->id, 'quantity' => 1, 'price' => 1]],
            ])
            ->assertCreated()
            ->assertJsonPath('total_harga', 30000);

        $order = Order::with('details')->firstOrFail();
        $this->assertSame(30000, $order->total_harga);
        $this->assertSame(2, $order->details->first()->jumlah);
        $this->assertSame(30000, $order->details->first()->subtotal);
        $this->assertSame([], session('cart', []));
    }

    public function test_cart_items_can_be_added_updated_and_removed_using_session_routes(): void
    {
        $menu = Menu::create([
            'nama_menu' => 'Es teh',
            'kategori' => 'Minuman',
            'harga' => 5000,
            'gambar' => 'https://example.test/es-teh.jpg',
            'deskripsi' => 'Teh dingin.',
        ]);

        $this->postJson('/api/cart/'.$menu->id)->assertCreated()->assertJsonPath('total_qty', 1);
        $this->postJson('/api/cart/'.$menu->id)->assertCreated()
            ->assertJsonPath('items.0.qty', 2)
            ->assertJsonPath('items.0.subtotal', 10000);
        $this->putJson('/api/cart/'.$menu->id, ['quantity' => 3])
            ->assertOk()
            ->assertJsonPath('total_price', 15000);
        $this->deleteJson('/api/cart/'.$menu->id)
            ->assertOk()
            ->assertJsonPath('total_qty', 0);
    }

    public function test_admin_routes_require_admin_role_and_menu_crud_uses_validation(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/admin/login')->assertOk();

        $customer = $this->makeUser();
        $this->actingAs($customer)->get('/admin')->assertForbidden();

        $admin = $this->makeUser('admin');
        $response = $this->actingAs($admin)->post('/admin/menus', [
            'nama_menu' => 'Es teh',
            'kategori' => 'Minuman',
            'harga' => 5000,
            'gambar' => 'https://example.test/es-teh.jpg',
            'deskripsi' => 'Teh dingin.',
        ]);
        $response->assertRedirect(route('admin.menus'));
        $this->assertDatabaseHas('menus', ['nama_menu' => 'Es teh', 'harga' => 5000]);

        $order = Order::create([
            'nomor_pesanan' => 'PGN-TEST123',
            'user_id' => $customer->id,
            'nama_pelanggan' => $customer->nama,
            'lokasi_pengambilan' => 'Meja 1',
            'total_harga' => 5000,
            'status' => 'pending',
        ]);
        $this->actingAs($customer)->get('/orders/'.$order->id)->assertOk();
        $this->getJson('/api/orders/'.$order->id)->assertOk()->assertJsonPath('orderNumber', '#PGN-TEST123');
        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->get('/admin/menus')->assertOk();
        $this->get('/admin/orders')->assertOk();
        $this->patch('/admin/orders/'.$order->id.'/status', ['status' => 'diproses'])
            ->assertRedirect(route('admin.orders'));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'diproses']);
    }

    public function test_registration_creates_a_customer_account_and_login_logout_work(): void
    {
        $this->post('/register', [
            'nama' => 'Mahasiswa Uji',
            'email' => 'mahasiswa@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'email' => 'mahasiswa@example.test',
            'role' => 'customer',
        ]);
        $this->post('/logout')->assertRedirect(route('home'));
        $this->post('/login', [
            'email' => 'mahasiswa@example.test',
            'password' => 'Password123!',
        ])->assertRedirect(route('home'));
    }

    private function makeUser(string $role = 'customer'): User
    {
        return User::create([
            'nama' => 'Pelanggan Uji',
            'email' => uniqid('panganin-', true).'@example.test',
            'password' => Hash::make('Password123!'),
            'role' => $role,
        ]);
    }
}
