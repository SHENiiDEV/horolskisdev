<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultShare;
use App\Models\VaultShareAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VaultAccessLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_access_logs_are_displayed_in_project_view()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $client = \App\Models\Client::create(['name' => 'Test Client', 'email' => 'client@example.com']);
        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Test Project',
            'status' => 'development',
            'pricing_type' => 'fixed',
            'domain' => 'example.com'
        ]);

        $vaultShare = VaultShare::create([
            'project_id' => $project->id,
            'name' => 'Test Share',
            'value' => 'secret-value',
            'is_active' => true,
        ]);

        // Simulate Access
        VaultShareAccess::create([
            'vault_share_id' => $vaultShare->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 Test Agent',
        ]);

        $response = $this->actingAs($user)
            ->get(route('projects.show', $project));

        $response->assertOk();
        $response->assertSee('Access Logs');
        $response->assertSee('Test Share');
        $response->assertSee('127.0.0.1');
        $response->assertSee('Mozilla/5.0 Test Agent');
    }
}
