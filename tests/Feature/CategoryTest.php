<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    // ---------- Authorization ----------

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/categories')->assertRedirect('/login');
    }

    public function test_admin_can_view_category_list(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/categories')
            ->assertOk()
            ->assertSee('Tambah Kategori');
    }

    public function test_technician_cannot_manage_categories(): void
    {
        // Teknisi boleh lihat daftar (via dropdown ticket), tapi tidak
        // boleh mengelola: tombol aksi tidak tampil & route create ditolak.
        $technician = User::factory()->technician()->create();
        $category = Category::factory()->create();

        $this->actingAs($technician)
            ->get('/categories')
            ->assertOk()
            ->assertDontSee('Tambah Kategori');

        $this->actingAs($technician)
            ->get('/categories/create')
            ->assertForbidden();

        $this->actingAs($technician)
            ->put("/categories/{$category->id}", ['name' => 'Hacked'])
            ->assertForbidden();

        $this->actingAs($technician)
            ->delete("/categories/{$category->id}")
            ->assertForbidden();
    }

    public function test_regular_user_cannot_manage_categories(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/categories/create')
            ->assertForbidden();

        $this->actingAs($user)
            ->post('/categories', ['name' => 'Baru'])
            ->assertForbidden();
    }

    // ---------- Create ----------

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/categories', ['name' => 'Access Point'])
            ->assertRedirect('/categories');

        $this->assertDatabaseHas('categories', [
            'name' => 'Access Point',
            'slug' => 'access-point',
        ]);
    }

    public function test_category_name_is_required(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/categories', ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertSame(0, Category::count());
    }

    public function test_category_name_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->create(['name' => 'Printer']);

        $this->actingAs($admin)
            ->post('/categories', ['name' => 'Printer'])
            ->assertSessionHasErrors('name');
    }

    public function test_category_name_minimum_length(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/categories', ['name' => 'A'])
            ->assertSessionHasErrors('name');
    }

    // ---------- Update ----------

    public function test_admin_can_update_category_and_slug_regenerates(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create([
            'name' => 'Wifi',
            'slug' => 'wifi',
        ]);

        $this->actingAs($admin)
            ->put("/categories/{$category->id}", ['name' => 'Wi-Fi & Jaringan'])
            ->assertRedirect('/categories');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Wi-Fi & Jaringan',
            'slug' => 'wi-fi-jaringan',
        ]);
    }

    public function test_update_can_keep_its_own_name(): void
    {
        // Edit tanpa ganti nama tidak boleh dianggap duplikat (ignore rule).
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'CCTV']);

        $this->actingAs($admin)
            ->put("/categories/{$category->id}", ['name' => 'CCTV'])
            ->assertRedirect('/categories');

        $this->assertSame(1, Category::count());
    }

    public function test_update_rejects_duplicate_name_from_other_category(): void
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->create(['name' => 'Printer']);
        $other = Category::factory()->create(['name' => 'Proyektor']);

        $this->actingAs($admin)
            ->put("/categories/{$other->id}", ['name' => 'Printer'])
            ->assertSessionHasErrors('name');
    }

    // ---------- Delete ----------

    public function test_admin_can_delete_unused_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(); // tanpa ticket

        $this->actingAs($admin)
            ->delete("/categories/{$category->id}")
            ->assertRedirect('/categories');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_with_tickets_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Ticket::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)
            ->delete("/categories/{$category->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_ticket_create_still_lists_categories(): void
    {
        // Form "Buat Ticket" tetap butuh daftar kategori (viewAny = semua login).
        $user = User::factory()->create();
        Category::factory()->create(['name' => 'Access Point']);

        $this->actingAs($user)
            ->get('/tickets/create')
            ->assertOk()
            ->assertSee('Access Point');
    }
}
