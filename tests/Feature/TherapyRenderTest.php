<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\TherapySeeder;
use App\Models\User;

class TherapyRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('PDO SQLite driver not available in this environment.');
        }

        parent::setUp();
    }

    public function test_public_therapy_page_shows_seeded_content()
    {
        // seed the therapy
        $this->seed(TherapySeeder::class);

        $response = $this->get('/therapy');

        $response->assertStatus(200);
        $response->assertSee('Entrenamiento para ir al baño');
        $response->assertSee('¿Está lista para dejar el pañal?');
    }

    public function test_admin_can_access_admin_therapies_index()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);

        $response = $this->get('/admin/therapies');

        $response->assertStatus(200);
    }
}
