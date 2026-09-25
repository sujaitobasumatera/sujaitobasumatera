<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\AppConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The admin config panel exists so settings can be changed without server
 * access. Most of these tests are about what it must REFUSE to do — the
 * convenience is only acceptable while credentials stay out of reach.
 */
class AppConfigTest extends TestCase
{
    use RefreshDatabase;

    protected $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create([
            'role' => 'superadmin',
            'email' => 'super@test.com',
        ]);
    }


    /**
     * Payload sah yang dibangun dari definisi field, bukan ditulis tangan.
     * Dengan begitu menambah field baru tidak membuat test ini gagal palsu.
     *
     * @param  array  $override
     * @return array
     */
    protected function validPayload(array $override = []): array
    {
        $payload = [];

        foreach (AppConfigService::fields() as $key => $field) {
            $payload[$key] = match ($field['type'] ?? 'text') {
                'boolean' => false,
                'number' => $this->validNumberFor($field),
                'select' => $field['options'][0],
                'email' => 'info@sujaitobasumatera.com',
                'url' => 'https://sujaitobasumatera.com',
                default => (string) (config(AppConfigService::paths($field)[0]) ?: 'Sujai Toba Sumatera'),
            };
        }

        return array_merge($payload, $override);
    }

    /**
     * Angka yang pasti lolos aturan field itu sendiri. Lingkungan test
     * menurunkan bcrypt rounds ke 4 demi kecepatan, jadi memakai nilai config
     * apa adanya akan gagal validasi min:10.
     *
     * @param  array  $field
     * @return int
     */
    protected function validNumberFor(array $field): int
    {
        $rules = $field['rules'] ?? '';
        $min = preg_match('/min:(\d+)/', $rules, $m) ? (int) $m[1] : 1;
        $max = preg_match('/max:(\d+)/', $rules, $m) ? (int) $m[1] : PHP_INT_MAX;

        $current = (int) config(AppConfigService::paths($field)[0]);

        return max($min, min($max, $current ?: $min));
    }

    public function test_every_field_points_at_a_real_config_key(): void
    {
        $fields = AppConfigService::fields();
        $this->assertNotEmpty($fields, 'Tidak ada field sama sekali; test ini tidak menguji apa-apa.');

        foreach ($fields as $key => $field) {
            $paths = AppConfigService::paths($field);

            $this->assertNotEmpty($paths, "Field '{$key}' tidak punya jalur config.");

            foreach ($paths as $path) {
                $this->assertTrue(
                    config()->has($path),
                    "Field '{$key}' menunjuk config('{$path}') yang tidak ada. ".
                    'Salah ketik seperti ini membuat field tampil di panel tapi tidak mengubah apa pun.'
                );
            }
        }
    }

    public function test_every_field_has_label_help_and_rules(): void
    {
        foreach (AppConfigService::fields() as $key => $field) {
            $this->assertNotEmpty($field['label'] ?? null, "Field '{$key}' tanpa label.");
            $this->assertNotEmpty($field['help'] ?? null, "Field '{$key}' tanpa penjelasan.");
            $this->assertNotEmpty($field['rules'] ?? null, "Field '{$key}' tanpa aturan validasi.");

            if (($field['type'] ?? '') === 'select') {
                $this->assertNotEmpty($field['options'] ?? null, "Field select '{$key}' tanpa pilihan.");
            }
        }
    }

    public function test_no_credential_slipped_into_the_field_list(): void
    {
        $denied = array_map('strtolower', config('editable.denied'));

        foreach (AppConfigService::fields() as $key => $field) {
            $this->assertNotContains(strtolower($key), $denied, "Field '{$key}' ada di daftar terlarang.");
            $this->assertStringNotContainsString('password', strtolower($key), "Field '{$key}' tampak seperti kredensial.");

            foreach (AppConfigService::paths($field) as $path) {
                $envish = strtolower(str_replace('.', '_', $path));
                $this->assertStringNotContainsString('secret', $envish, "Field '{$key}' tampak seperti kredensial.");
                $this->assertStringNotContainsString('password', $envish, "Field '{$key}' menunjuk jalur kredensial.");
            }
        }
    }

    public function test_superadmin_can_open_the_panel(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->get(route('admin.settings.app-config.index'));

        $response->assertOk();
        $response->assertSee('Nama Aplikasi');
    }

    public function test_other_admin_roles_are_refused(): void
    {
        foreach (['admin_tour', 'admin_umum'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('admin.settings.app-config.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->post(route('admin.settings.app-config.update'), ['app_name' => 'Diretas'])
                ->assertForbidden();
        }
    }

    public function test_guests_are_refused(): void
    {
        $this->get(route('admin.settings.app-config.index'))->assertRedirect();
    }

    public function test_saved_value_overrides_config(): void
    {
        $this->actingAs($this->superadmin)
            ->post(route('admin.settings.app-config.update'), $this->validPayload([
                'app_name' => 'Sujai Laketoba Travel',
                'session_lifetime' => 120,
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        AppConfigService::apply();

        $this->assertSame('Sujai Laketoba Travel', config('app.name'));
        $this->assertSame(120, config('session.lifetime'));
    }

    public function test_credentials_are_never_rendered(): void
    {
        $dbPassword = (string) config('database.connections.mysql.password');
        $appKey = (string) config('app.key');

        // Without this guard the assertions below would pass against an empty
        // string and prove nothing — a security test that cannot fail is worse
        // than no test, because it reads as coverage.
        $this->assertNotSame('', $dbPassword, 'DB password kosong di lingkungan test; assertDontSee di bawah jadi tidak bermakna.');
        $this->assertNotSame('', $appKey, 'APP_KEY kosong di lingkungan test; assertDontSee di bawah jadi tidak bermakna.');

        $response = $this->actingAs($this->superadmin)
            ->get(route('admin.settings.app-config.index'));

        // The real values must not leak, whatever the page happens to list.
        $response->assertDontSee($dbPassword);
        $response->assertDontSee($appKey);

        // No input field may exist for a denied key.
        $response->assertDontSee('name="DB_PASSWORD"', false);
        $response->assertDontSee('name="APP_KEY"', false);
        $response->assertDontSee('name="db_password"', false);
    }

    public function test_crafted_post_cannot_introduce_a_denied_key(): void
    {
        $this->actingAs($this->superadmin)->post(
            route('admin.settings.app-config.update'),
            $this->validPayload([
                'app_name' => 'Sujai Laketoba',
                // Tidak ditawarkan form. Harus dibuang, bukan disimpan.
                'DB_PASSWORD' => 'dibajak',
                'APP_KEY' => 'base64:dibajak',
                'db_password' => 'dibajak',
            ])
        )->assertSessionHasNoErrors();

        $stored = AppConfigService::stored();

        $this->assertArrayNotHasKey('DB_PASSWORD', $stored);
        $this->assertArrayNotHasKey('APP_KEY', $stored);
        $this->assertArrayNotHasKey('db_password', $stored);
        $this->assertSame('Sujai Laketoba', $stored['app_name']);
    }

    public function test_denylist_wins_even_if_a_credential_is_added_to_the_field_list(): void
    {
        // Simulates someone later pasting a credential into config/editable.php.
        // The service must still refuse to surface it.
        config([
            'editable.fields.db_password' => [
                'config' => 'database.connections.mysql.password',
                'label' => 'DB Password',
                'help' => 'seharusnya tidak pernah muncul',
                'type' => 'text',
                'rules' => 'nullable|string',
                'group' => 'Umum',
            ],
            'editable.denied' => array_merge(config('editable.denied'), ['DB_PASSWORD']),
        ]);

        $this->assertArrayNotHasKey('db_password', AppConfigService::fields());

        AppConfigService::save(['db_password' => 'dibajak']);

        $this->assertArrayNotHasKey('db_password', AppConfigService::stored());
    }

    public function test_env_file_is_never_written(): void
    {
        $before = file_get_contents(base_path('.env'));

        $this->actingAs($this->superadmin)
            ->post(route('admin.settings.app-config.update'), $this->validPayload(['app_name' => 'Nama Baru']))
            ->assertSessionHasNoErrors();

        $this->assertSame($before, file_get_contents(base_path('.env')));
        $this->assertDatabaseHas('settings', ['key' => AppConfigService::STORAGE_KEY]);
    }

    public function test_invalid_input_is_rejected(): void
    {
        $this->actingAs($this->superadmin)->post(route('admin.settings.app-config.update'), [
            'app_name' => '',
            'app_url' => 'bukan-url',
            'log_level' => 'chatty',
            'mail_from_address' => 'bukan-email',
            'mail_from_name' => 'Sujai',
            'session_lifetime' => 1,
        ])->assertSessionHasErrors(['app_name', 'app_url', 'log_level', 'mail_from_address', 'session_lifetime']);

        $this->assertNull(Setting::where('key', AppConfigService::STORAGE_KEY)->first());
    }
}
