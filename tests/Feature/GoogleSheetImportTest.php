<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleSheetImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_import_vaults_from_google_sheet()
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

        $csvContent = "Service,Username,Password,Extra\n" .
            "Gmail,test@gmail.com,pass123,+1234567890\n" .
            "Namecheap,myuser,pass456,\n" .
            "privateemail,info@domain.com,pass789,\n" .
            "WP,admin,wp_pass,https://example.com/wp-admin";

        Http::fake([
            'docs.google.com/*' => Http::response($csvContent, 200),
        ]);

        $response = $this->actingAs($user)
            ->post(route('projects.vaults.import', $project), [
                'sheet_url' => 'https://docs.google.com/spreadsheets/d/test-id/edit?gid=0',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('vaults', 4);

        // Check Gmail
        $gmail = Vault::where('type', 'gmail')->first();
        $this->assertNotNull($gmail);
        $this->assertEquals('Gmail', $gmail->name);
        $fields = collect($gmail->fields);
        $this->assertEquals('test@gmail.com', $fields->firstWhere('name', 'Email')['value']);
        $this->assertEquals('+1234567890', $fields->firstWhere('name', 'Phone')['value']);

        // Check Namecheap
        $hosting = Vault::where('type', 'hosting')->first();
        $this->assertNotNull($hosting);
        $this->assertEquals('Namecheap', $hosting->name);
        $fields = collect($hosting->fields);
        $this->assertEquals('myuser', $fields->firstWhere('name', 'Username')['value']);
        $this->assertEquals('https://www.namecheap.com/myaccount/login/?ReturnUrl=%2f', $fields->firstWhere('name', 'Login URL')['value']);

        // Check WP
        $wp = Vault::where('type', 'wordpress')->first();
        $this->assertNotNull($wp);
        $this->assertEquals('Wordpress Admin Login', $wp->name);
        $fields = collect($wp->fields);
        $this->assertEquals('https://example.com/wp-admin', $fields->firstWhere('name', 'Login URL')['value']);
    }

    public function test_handles_invalid_url()
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
        ]);

        $response = $this->actingAs($user)
            ->post(route('projects.vaults.import', $project), [
                'sheet_url' => 'not-a-url',
            ]);

        $response->assertSessionHasErrors('sheet_url');
    }

    public function test_handles_http_failure()
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
        ]);

        Http::fake([
            'docs.google.com/*' => Http::response('Error', 500),
        ]);

        $response = $this->actingAs($user)
            ->post(route('projects.vaults.import', $project), [
                'sheet_url' => 'https://docs.google.com/spreadsheets/d/test-id/edit',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
