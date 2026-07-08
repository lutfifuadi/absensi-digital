<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFilterSortTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $user1;
    protected User $user2;
    protected User $user3;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Buat user Super Admin
        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin Test',
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'role' => User::ROLE_SUPER_ADMIN,
            'roles' => [User::ROLE_SUPER_ADMIN],
            'created_at' => now()->subDays(10),
        ]);

        // 2. Buat beberapa dummy users dengan data nama, email, username, role, dan created_at yang bervariasi
        $this->user1 = User::factory()->create([
            'name' => 'Budi Santoso',
            'username' => 'budis',
            'email' => 'budi@example.com',
            'role' => User::ROLE_GURU,
            'roles' => [User::ROLE_GURU],
            'created_at' => now()->subDays(5), // Tanggal join lebih lama dibanding user2
        ]);

        $this->user2 = User::factory()->create([
            'name' => 'Andi Wijaya',
            'username' => 'andiw',
            'email' => 'andi@example.com',
            'role' => User::ROLE_SISWA,
            'roles' => [User::ROLE_SISWA],
            'created_at' => now()->subDays(2), // Tanggal join lebih baru
        ]);

        $this->user3 = User::factory()->create([
            'name' => 'Citra Lestari',
            'username' => 'citral',
            'email' => 'citra@example.com',
            'role' => User::ROLE_OPERATOR,
            'roles' => [User::ROLE_OPERATOR],
            'created_at' => now()->subDays(15), // Tanggal join paling lama
        ]);
    }

    /**
     * Test case untuk: Pencarian Teks (search)
     */
    public function test_user_can_search_by_text(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.users.index', ['search' => 'Budi']));

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Andi Wijaya');
        $response->assertDontSee('Citra Lestari');
    }

    /**
     * Test case untuk: Filter Role (role)
     */
    public function test_user_can_filter_by_role(): void
    {
        // Filter guru
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.users.index', ['role' => User::ROLE_GURU]));

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Andi Wijaya');
        $response->assertDontSee('Citra Lestari');
    }

    /**
     * Test case untuk: Filter Tanggal Join (start_date, end_date)
     */
    public function test_user_can_filter_by_join_date(): void
    {
        // Kita ingin memfilter data yang dibuat antara 6 hari yang lalu sampai 1 hari yang lalu
        // Ini harusnya mencakup: Budi (5 hari lalu), Andi (2 hari lalu)
        // Dan mengecualikan: Citra (15 hari lalu), Super Admin (10 hari lalu)
        
        $startDate = now()->subDays(6)->format('Y-m-d');
        $endDate = now()->subDays(1)->format('Y-m-d');

        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.users.index', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]));

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertSee('Andi Wijaya');
        $response->assertDontSee('Citra Lestari');
    }

    /**
     * Test case untuk: Sortir Nama (sort_by=name&sort_direction=asc|desc)
     */
    public function test_user_can_sort_by_name_asc_and_desc(): void
    {
        // Ascending: Andi Wijaya -> Budi Santoso -> Citra Lestari -> Super Admin Test
        $responseAsc = $this->actingAs($this->superAdmin)
            ->get(route('admin.users.index', [
                'sort_by' => 'name',
                'sort_direction' => 'asc'
            ]));

        $responseAsc->assertStatus(200);
        
        $usersAsc = $responseAsc->viewData('users');
        $this->assertNotNull($usersAsc);
        
        // Dapatkan array nama dalam urutan
        $namesAsc = $usersAsc->pluck('name')->toArray();
        
        // Cari indeks nama-nama tersebut
        $idxAndi = array_search('Andi Wijaya', $namesAsc);
        $idxBudi = array_search('Budi Santoso', $namesAsc);
        $idxCitra = array_search('Citra Lestari', $namesAsc);
        $idxSuper = array_search('Super Admin Test', $namesAsc);

        $this->assertTrue($idxAndi !== false);
        $this->assertTrue($idxBudi !== false);
        $this->assertTrue($idxCitra !== false);
        $this->assertTrue($idxSuper !== false);

        $this->assertTrue($idxAndi < $idxBudi, 'Andi harus muncul sebelum Budi (ASC)');
        $this->assertTrue($idxBudi < $idxCitra, 'Budi harus muncul sebelum Citra (ASC)');
        $this->assertTrue($idxCitra < $idxSuper, 'Citra harus muncul sebelum Super Admin (ASC)');

        // Descending: Super Admin Test -> Citra Lestari -> Budi Santoso -> Andi Wijaya
        $responseDesc = $this->actingAs($this->superAdmin)
            ->get(route('admin.users.index', [
                'sort_by' => 'name',
                'sort_direction' => 'desc'
            ]));

        $responseDesc->assertStatus(200);
        
        $usersDesc = $responseDesc->viewData('users');
        $namesDesc = $usersDesc->pluck('name')->toArray();

        $idxAndiDesc = array_search('Andi Wijaya', $namesDesc);
        $idxBudiDesc = array_search('Budi Santoso', $namesDesc);
        $idxCitraDesc = array_search('Citra Lestari', $namesDesc);
        $idxSuperDesc = array_search('Super Admin Test', $namesDesc);

        $this->assertTrue($idxSuperDesc < $idxCitraDesc, 'Super Admin harus muncul sebelum Citra (DESC)');
        $this->assertTrue($idxCitraDesc < $idxBudiDesc, 'Citra harus muncul sebelum Budi (DESC)');
        $this->assertTrue($idxBudiDesc < $idxAndiDesc, 'Budi harus muncul sebelum Andi (DESC)');
    }

    /**
     * Test case untuk: Sortir Tanggal Join (sort_by=created_at&sort_direction=asc|desc)
     */
    public function test_user_can_sort_by_created_at_asc_and_desc(): void
    {
        // Urutan tanggal join terlama ke terbaru (ASC):
        // 1. Citra (15 hari lalu)
        // 2. Super Admin (10 hari lalu)
        // 3. Budi (5 hari lalu)
        // 4. Andi (2 hari lalu)
        $responseAsc = $this->actingAs($this->superAdmin)
            ->get(route('admin.users.index', [
                'sort_by' => 'created_at',
                'sort_direction' => 'asc'
            ]));

        $responseAsc->assertStatus(200);
        
        $usersAsc = $responseAsc->viewData('users');
        $namesAsc = $usersAsc->pluck('name')->toArray();

        $idxCitra = array_search('Citra Lestari', $namesAsc);
        $idxSuper = array_search('Super Admin Test', $namesAsc);
        $idxBudi = array_search('Budi Santoso', $namesAsc);
        $idxAndi = array_search('Andi Wijaya', $namesAsc);

        $this->assertTrue($idxCitra < $idxSuper, 'Citra harus muncul sebelum Super Admin (ASC)');
        $this->assertTrue($idxSuper < $idxBudi, 'Super Admin harus muncul sebelum Budi (ASC)');
        $this->assertTrue($idxBudi < $idxAndi, 'Budi harus muncul sebelum Andi (ASC)');

        // Urutan tanggal join terbaru ke terlama (DESC):
        // 1. Andi (2 hari lalu)
        // 2. Budi (5 hari lalu)
        // 3. Super Admin (10 hari lalu)
        // 4. Citra (15 hari lalu)
        $responseDesc = $this->actingAs($this->superAdmin)
            ->get(route('admin.users.index', [
                'sort_by' => 'created_at',
                'sort_direction' => 'desc'
            ]));

        $responseDesc->assertStatus(200);
        
        $usersDesc = $responseDesc->viewData('users');
        $namesDesc = $usersDesc->pluck('name')->toArray();

        $idxCitraDesc = array_search('Citra Lestari', $namesDesc);
        $idxSuperDesc = array_search('Super Admin Test', $namesDesc);
        $idxBudiDesc = array_search('Budi Santoso', $namesDesc);
        $idxAndiDesc = array_search('Andi Wijaya', $namesDesc);

        $this->assertTrue($idxAndiDesc < $idxBudiDesc, 'Andi harus muncul sebelum Budi (DESC)');
        $this->assertTrue($idxBudiDesc < $idxSuperDesc, 'Budi harus muncul sebelum Super Admin (DESC)');
        $this->assertTrue($idxSuperDesc < $idxCitraDesc, 'Super Admin harus muncul sebelum Citra (DESC)');
    }

    /**
     * Test case untuk: Request AJAX yang mengembalikan partial HTML.
     */
    public function test_ajax_request_returns_partial_html(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.users.index'), [
                'X-Requested-With' => 'XMLHttpRequest'
            ]);

        $response->assertStatus(200);
        
        // Memastikan yang dikembalikan adalah view partial table, bukan index yang memiliki layout lengkap.
        // Kita bisa mengecek keberadaan tag header/layout umum dari template admin.users.index.
        // Jika view partial table, dia hanya akan menampilkan baris-baris tabel.
        // Mari kita cek bahwa content mengandung data user tetapi tidak mengandung layout index utama.
        $response->assertSee('Budi Santoso');
        $response->assertSee('Andi Wijaya');
        $response->assertSee('Citra Lestari');
        
        // Tag filter sidebar atau elemen luar index.blade.php yang tidak ada di table.blade.php
        $response->assertDontSee('Manajemen User');
        $response->assertDontSee('das-content-header');
    }
}
