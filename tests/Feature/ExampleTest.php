<?php

namespace Tests\Feature;

use App\Models\Pengaturan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_responds_successfully(): void
    {
        // Setup default configuration
        Pengaturan::updateOrCreate(['key' => 'tampilkan_beranda'], ['value' => 'Ya']);

        $response = $this->get('/');

        $this->assertTrue(
            in_array($response->status(), [200, 302]),
            'Home page should return 200 or 302 (depending on tampilkan_beranda setting)'
        );
    }
}
