<?php

namespace Tests\Feature;

use Tests\TestCase;

class TranslationParityTest extends TestCase
{
    private function keys(string $locale): array
    {
        $path = base_path("lang/{$locale}.json");
        $data = json_decode(file_get_contents($path), true);
        $this->assertIsArray($data, "lang/{$locale}.json harus JSON valid");

        return array_keys($data);
    }

    /** @test */
    public function test_semua_locale_punya_kunci_yang_sama()
    {
        $en = $this->keys('en');
        $my = $this->keys('my');
        $id = $this->keys('id');

        $this->assertSame([], array_values(array_diff($en, $my)), 'Kunci ada di en tapi hilang di my');
        $this->assertSame([], array_values(array_diff($my, $en)), 'Kunci ada di my tapi hilang di en');
        $this->assertSame([], array_values(array_diff($en, $id)), 'Kunci ada di en tapi hilang di id');
    }

    /** @test */
    public function test_kunci_publik_yang_dulu_hilang_kini_ada()
    {
        $en = $this->keys('en');

        foreach (['Home', 'HUBUNGI KAMI!', 'Halaman Tidak Ditemukan', 'Testimoni Wisatawan', 'Lacak Pesanan'] as $key) {
            $this->assertContains($key, $en, "Kunci publik '{$key}' harus ada di en.json");
        }
    }
}
