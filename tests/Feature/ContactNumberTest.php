<?php

namespace Tests\Feature;

use App\Helpers\ContactHelper;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Nomor yang tampil harus selalu sama dengan nomor yang dihubungi.
 *
 * Sebelumnya keduanya punya nilai bawaan berbeda, sehingga satu tombol bisa
 * menuliskan '+62 813-2388-8207' sambil menaut ke wa.me/6282277848855. Di
 * halaman pembayaran, ketidakcocokan seperti itu terbaca sebagai penipuan.
 */
class ContactNumberTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('contact_whatsapp_digits');
    }

    public function test_displayed_number_matches_the_linked_number(): void
    {
        $digits = ContactHelper::whatsappDigits();
        $display = ContactHelper::whatsappDisplay();
        $link = ContactHelper::whatsappLink();

        // Angka pada teks, setelah dibuang format, harus identik dengan tautan.
        $this->assertSame($digits, preg_replace('/[^0-9]/', '', $display));
        $this->assertSame('https://wa.me/'.$digits, $link);
    }

    public function test_falls_back_to_the_official_number(): void
    {
        $this->assertSame('6282277848855', ContactHelper::whatsappDigits());
        $this->assertSame('+62 822-7784-8855', ContactHelper::whatsappDisplay());
    }

    public function test_setting_overrides_both_text_and_link_together(): void
    {
        Setting::create(['key' => 'general', 'value' => ['contact_whatsapp' => '+62 811-2233-4455']]);
        Cache::forget('contact_whatsapp_digits');

        $this->assertSame('6281122334455', ContactHelper::whatsappDigits());
        $this->assertSame('+62 811-2233-4455', ContactHelper::whatsappDisplay());
        $this->assertStringContainsString('6281122334455', ContactHelper::whatsappLink());
    }

    public function test_local_format_is_converted_for_wa_me(): void
    {
        // wa.me menolak nomor lokal 08xx; harus jadi 62xx atau tautannya mati.
        Setting::create(['key' => 'general', 'value' => ['contact_whatsapp' => '0822 7784 8855']]);
        Cache::forget('contact_whatsapp_digits');

        $this->assertSame('6282277848855', ContactHelper::whatsappDigits());
    }

    public function test_malformed_number_falls_back_rather_than_producing_a_dead_link(): void
    {
        Setting::create(['key' => 'general', 'value' => ['contact_whatsapp' => '123']]);
        Cache::forget('contact_whatsapp_digits');

        $this->assertSame(ContactHelper::DEFAULT_WHATSAPP, ContactHelper::whatsappDigits());
    }

    public function test_specialist_falls_back_to_the_main_number(): void
    {
        $this->assertSame(ContactHelper::whatsappDigits(), ContactHelper::specialistDigits());
    }

    public function test_no_page_shows_a_number_it_does_not_link_to(): void
    {
        $digits = ContactHelper::whatsappDigits();

        $totalLinks = 0;

        foreach ([route('tour.packages'), route('payment'), route('terms'), route('about')] as $url) {
            $response = $this->get($url);

            // Tanpa dua penjaga ini, halaman yang 404 atau kosong akan membuat
            // assertion di bawah lolos tanpa menguji apa pun.
            $response->assertOk();
            $html = $response->getContent();
            $this->assertGreaterThan(1000, strlen($html), "Halaman {$url} nyaris kosong; pemeriksaan di bawah jadi tidak bermakna.");

            // Nomor lama tidak boleh muncul di mana pun lagi.
            $this->assertStringNotContainsString('813-2388-8207', $html, "Nomor lama masih ada di {$url}");

            // Setiap tautan wa.me harus menuju nomor resmi.
            preg_match_all('#wa\.me/(\d+)#', $html, $m);
            foreach ($m[1] as $linked) {
                $this->assertSame($digits, $linked, "Tautan wa.me di {$url} menuju nomor lain");
            }
            $totalLinks += count($m[1]);
        }

        // Dan harus benar-benar ADA tautan wa.me yang diperiksa.
        $this->assertGreaterThan(0, $totalLinks, 'Tidak satu pun tautan wa.me ditemukan; test ini tidak menguji apa-apa.');
    }
}
