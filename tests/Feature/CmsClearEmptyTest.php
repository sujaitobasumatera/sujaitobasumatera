<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsClearEmptyTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'superadmin',
            'email' => 'admin@test.com',
        ]);

        Setting::create([
            'key' => 'cms_tour',
            'value' => [
                'show_hero' => true,
                'show_stats' => true,
                'featured_package_ids' => [1, 2, 3],
                'homepage_slides' => [
                    ['title' => 'A'],
                    ['title' => 'B'],
                    ['title' => 'C'],
                ],
            ],
        ]);
    }

    private function saved(): array
    {
        return Setting::where('key', 'cms_tour')->first()->value;
    }

    /** @test */
    public function test_menghapus_sebagian_slide_tersimpan()
    {
        $this->actingAs($this->admin)->post('/admin/cms-save/cms_tour', [
            '_clear_if_empty' => 'homepage_slides,testimonials,featured_package_ids',
            'homepage_slides' => [['title' => 'A'], ['title' => 'C']],
        ]);

        $this->assertSame(['A', 'C'], array_column($this->saved()['homepage_slides'], 'title'));
    }

    /** @test */
    public function test_menghapus_semua_slide_tersimpan()
    {
        // Browser tidak mengirim key homepage_slides sama sekali saat daftarnya kosong.
        $this->actingAs($this->admin)->post('/admin/cms-save/cms_tour', [
            '_clear_if_empty' => 'homepage_slides,testimonials,featured_package_ids',
        ]);

        $this->assertSame([], $this->saved()['homepage_slides']);
    }

    /** @test */
    public function test_melepas_semua_pin_paket_unggulan_tersimpan()
    {
        $this->actingAs($this->admin)->post('/admin/cms-save/cms_tour', [
            '_clear_if_empty' => 'homepage_slides,testimonials,featured_package_ids',
            'homepage_slides' => [['title' => 'A']],
        ]);

        $this->assertSame([], $this->saved()['featured_package_ids']);
    }

    /** @test */
    public function test_mematikan_toggle_hero_dan_statistik_tersimpan()
    {
        // Meniru hidden fallback value="0" yang kini ada di form.
        $this->actingAs($this->admin)->post('/admin/cms-save/cms_tour', [
            '_clear_if_empty' => 'homepage_slides,testimonials,featured_package_ids',
            'show_hero' => '0',
            'show_stats' => '0',
            'homepage_slides' => [['title' => 'A']],
        ]);

        $this->assertFalse((bool) $this->saved()['show_hero']);
        $this->assertFalse((bool) $this->saved()['show_stats']);
    }

    /** @test */
    public function test_penanda_clear_if_empty_tidak_ikut_tersimpan()
    {
        $this->actingAs($this->admin)->post('/admin/cms-save/cms_tour', [
            '_clear_if_empty' => 'homepage_slides',
            'homepage_slides' => [['title' => 'A']],
        ]);

        $this->assertArrayNotHasKey('_clear_if_empty', $this->saved());
    }
}
