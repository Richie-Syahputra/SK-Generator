<?php

namespace Tests\Feature;

use Database\Seeders\JenisSkSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        Artisan::call('db:seed', ['--class' => JenisSkSeeder::class]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
