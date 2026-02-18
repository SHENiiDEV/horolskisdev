<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\VaultShare;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class VaultShareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_user_can_create_share_link()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $client = \App\Models\Client::create(['name' => 'Test Client']);
        $project = Project::create([
            'name' => 'Test Project',
            'client_id' => $client->id,
            'status' => 'live',
        ]);

        $response = $this->actingAs($user)->postJson(route('vault-shares.store'), [
            'project_id' => $project->id,
            'value' => 'secret-password',
            'name' => 'My Secret',
            'expires_in' => 60,
            'allowed_views' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['link', 'expires_at']);

        $this->assertDatabaseHas('vault_shares', [
            'project_id' => $project->id,
            'name' => 'My Secret',
            'allowed_views' => 1,
        ]);

        $share = VaultShare::first();
        $this->assertEquals('secret-password', $share->value);
    }

    public function test_guest_can_view_valid_share_page()
    {
        $client = \App\Models\Client::create(['name' => 'Test Client']);
        $project = Project::create([
            'name' => 'Test Project',
            'client_id' => $client->id,
            'status' => 'live',
        ]);

        $share = VaultShare::create([
            'project_id' => $project->id,
            'value' => 'secret',
            'allowed_views' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('shares.show', $share->id));

        $response->assertStatus(200);
        $response->assertSee('Secure Credential');
    }

    public function test_guest_can_reveal_secret()
    {
        $client = \App\Models\Client::create(['name' => 'Test Client']);
        $project = Project::create([
            'name' => 'Test Project',
            'client_id' => $client->id,
            'status' => 'live',
        ]);

        $share = VaultShare::create([
            'project_id' => $project->id,
            'value' => 'super-secret-value',
            'allowed_views' => 2,
            'is_active' => true,
        ]);

        $response = $this->post(route('shares.reveal', $share->id));

        $response->assertStatus(200);
        $response->assertViewIs('vault-shares.show');
        $response->assertViewHas('secret', 'super-secret-value');
        $response->assertViewHas('revealed', true);

        $this->assertEquals(1, $share->fresh()->views);
        $this->assertTrue($share->fresh()->is_active);
    }

    public function test_share_deactivates_after_max_views()
    {
        $client = \App\Models\Client::create(['name' => 'Test Client']);
        $project = Project::create([
            'name' => 'Test Project',
            'client_id' => $client->id,
            'status' => 'live',
        ]);

        $share = VaultShare::create([
            'project_id' => $project->id,
            'value' => 'secret',
            'allowed_views' => 1,
            'views' => 0,
            'is_active' => true,
        ]);

        // First view (reveal)
        $this->post(route('shares.reveal', $share->id));

        $this->assertFalse($share->fresh()->is_active);

        // Second view - should fail
        $response = $this->post(route('shares.reveal', $share->id));
        $response->assertStatus(404);
    }

    public function test_expired_share_returns_404()
    {
        $client = \App\Models\Client::create(['name' => 'Test Client']);
        $project = Project::create([
            'name' => 'Test Project',
            'client_id' => $client->id,
            'status' => 'live',
        ]);

        $share = VaultShare::create([
            'project_id' => $project->id,
            'value' => 'secret',
            'allowed_views' => 5,
            'expires_at' => now()->subMinute(),
            'is_active' => true,
        ]);

        $response = $this->get(route('shares.show', $share->id));
        $response->assertStatus(404);
    }
}
