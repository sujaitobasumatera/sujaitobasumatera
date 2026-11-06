<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TestimonialSectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function setTestimonials(array $testimonials, array $extra = []): void
    {
        Setting::updateOrCreate(
            ['key' => 'cms_tour'],
            ['value' => array_merge(['show_testimonials' => '1', 'testimonials' => $testimonials], $extra)]
        );
        Cache::flush();
    }

    public function test_blank_testimonial_rows_are_never_rendered(): void
    {
        // Form admin menyimpan satu baris begitu tombol "Tambah Ulasan"
        // ditekan, jadi baris yang belum sempat diisi ikut tersimpan. Tanpa
        // saringan, baris itu terbit sebagai kartu hampa -- lengkap dengan
        // bintang lima dan foto profil kosong.
        $this->setTestimonials([
            ['name' => 'Andini Wijaya', 'text' => 'Pemandunya ramah dan tepat waktu.', 'location' => 'Medan'],
            ['name' => '', 'text' => '', 'location' => ''],
            ['name' => '   ', 'text' => 'Ada teks tapi tanpa nama.'],
            ['name' => 'Tanpa Ulasan', 'text' => '   '],
        ]);

        $html = $this->get(route('index'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<figure'), 'hanya satu ulasan yang lengkap');
        $this->assertStringContainsString('Andini Wijaya', $html);
        $this->assertStringNotContainsString('Tanpa Ulasan', $html);
        $this->assertStringNotContainsString('Ada teks tapi tanpa nama.', $html);
    }

    public function test_section_disappears_when_every_row_is_blank(): void
    {
        // Semua baris kosong sama saja dengan tidak ada testimoni. Judul
        // besar di atas deretan kartu kosong lebih buruk daripada tidak ada
        // bagiannya sama sekali.
        $this->setTestimonials([
            ['name' => '', 'text' => ''],
            ['name' => '', 'text' => ''],
        ]);

        $this->get(route('index'))
            ->assertOk()
            ->assertDontSee('Apa Kata Mereka', false);
    }

    public function test_navigation_buttons_hide_when_there_is_nothing_to_scroll(): void
    {
        // Tombol geser yang tidak menggeser apa pun lebih membingungkan
        // daripada tidak ada tombol.
        $this->setTestimonials([
            ['name' => 'Satu Saja', 'text' => 'Ulasan tunggal.'],
        ]);

        $this->get(route('index'))
            ->assertOk()
            ->assertDontSee('Testimoni berikutnya', false);
    }

    public function test_heading_comes_from_the_admin_panel(): void
    {
        $this->setTestimonials(
            [['name' => 'Andini Wijaya', 'text' => 'Pemandunya ramah.']],
            [
                'testimonials_eyebrow' => 'Kata Tamu Kami',
                'testimonials_title' => 'Cerita dari Danau Toba',
                'testimonials_subtitle' => 'Ditulis sendiri oleh tamu yang sudah berjalan bersama kami.',
            ]
        );

        $this->get(route('index'))
            ->assertOk()
            ->assertSee('Kata Tamu Kami', false)
            ->assertSee('Cerita dari Danau Toba', false)
            ->assertSee('Ditulis sendiri oleh tamu yang sudah berjalan bersama kami.', false);
    }

    public function test_section_can_be_switched_off_entirely(): void
    {
        Setting::updateOrCreate(['key' => 'cms_tour'], ['value' => [
            'show_testimonials' => '0',
            'testimonials' => [['name' => 'Andini Wijaya', 'text' => 'Pemandunya ramah.']],
        ]]);
        Cache::flush();

        $this->get(route('index'))
            ->assertOk()
            ->assertDontSee('Andini Wijaya', false);
    }
}
