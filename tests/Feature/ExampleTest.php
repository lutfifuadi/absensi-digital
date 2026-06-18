<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_responds_successfully(): void
    {
        $response = $this->get('/');

        $this->assertTrue(
            in_array($response->status(), [200, 302]),
            'Home page should return 200 or 302 (depending on tampilkan_beranda setting)'
        );
    }
}
