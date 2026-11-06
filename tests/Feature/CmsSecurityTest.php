<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CmsSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role = 'superadmin'): User
    {
        return User::factory()->create(['role' => $role]);
    }

    /** @test */
    public function test_cms_save_menolak_key_di_luar_whitelist()
    {
        $this->actingAs($this->admin())
            ->post('/admin/cms-save/general', ['foo' => 'bar'])
            ->assertNotFound();

        // Key global 'general' tidak boleh terbuat lewat jalur ini.
        $this->assertDatabaseMissing('settings', ['key' => 'general']);
    }

    /** @test */
    public function test_cms_save_menerima_key_yang_diizinkan()
    {
        $this->actingAs($this->admin())
            ->post('/admin/cms-save/cms_tour', ['hero_title' => 'Halo'])
            ->assertRedirect();

        $this->assertSame('Halo', Setting::where('key', 'cms_tour')->first()->value['hero_title']);
    }

    /** @test */
    public function test_api_dashboard_menolak_token_non_admin()
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'user']));
        $this->getJson('/api/dashboard')->assertForbidden();
    }

    /** @test */
    public function test_api_dashboard_mengizinkan_token_admin()
    {
        Sanctum::actingAs($this->admin('admin_umum'));
        $this->getJson('/api/dashboard')->assertOk();
    }
}
