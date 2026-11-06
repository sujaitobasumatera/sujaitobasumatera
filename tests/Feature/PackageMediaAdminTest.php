<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PackageMediaAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function paket(array $extra = []): Package
    {
        return Package::create(array_merge([
            'slug' => 'paket-media-admin',
            'name' => 'Paket Media Admin',
            'description' => 'Deskripsi uji.',
            'price' => 400,
            'duration' => '3D2N',
            'images' => [],
            'includes' => [],
            'excludes' => [],
            'status' => 'active',
        ], $extra));
    }

    /** Field wajib form paket, di luar media yang sedang diuji. */
    private function dasar(Package $paket): array
    {
        return [
            'name' => $paket->name,
            'price' => $paket->price,
            'status' => 'active',
            'duration' => $paket->duration,
        ];
    }

    public function test_form_create_dan_edit_sama_sama_menampilkan_field_media(): void
    {
        // Blok media dipasang lewat satu partial bersama. Kalau salah satu form
        // lupa memanggilnya, gejalanya cuma "field-nya tidak ada di sini" --
        // tanpa error apa pun.
        $paket = $this->paket();
        $admin = $this->admin();

        $halaman = [
            'edit' => $this->actingAs($admin)->get(route('admin.packages.edit', $paket))->assertOk()->getContent(),
            'create' => $this->actingAs($admin)->get(route('admin.packages.create'))->assertOk()->getContent(),
        ];

        foreach ($halaman as $nama => $html) {
            $this->assertStringContainsString('Media Tambahan', $html, "judul blok media hilang di form {$nama}");
            $this->assertStringContainsString('Tambah Tautan Video', $html, "tombol tambah tautan hilang di form {$nama}");
            $this->assertStringContainsString('name="video_files[]"', $html, "input unggah video hilang di form {$nama}");
            $this->assertStringContainsString('name="mapEmbed"', $html, "input peta hilang di form {$nama}");
            $this->assertStringContainsString('name="brochure_file"', $html, "input brosur hilang di form {$nama}");
        }
    }

    public function test_admin_bisa_menyimpan_tautan_dan_berkas_video_sekaligus(): void
    {
        Storage::fake('public');
        $paket = $this->paket();

        $this->actingAs($this->admin())
            ->put(route('admin.packages.update', $paket), $this->dasar($paket) + [
                'video_links' => [
                    ['src' => 'https://youtu.be/abc123XYZ', 'title' => 'Cuplikan'],
                    ['src' => '', 'title' => 'baris kosong diabaikan'],
                ],
                'video_files' => [UploadedFile::fake()->create('drone.mp4', 512, 'video/mp4')],
            ])
            ->assertRedirect();

        $videos = $paket->fresh()->videos;

        $this->assertCount(2, $videos, 'baris tautan kosong seharusnya tidak ikut tersimpan');

        $berkas = collect($videos)->firstWhere('type', 'file');
        $tautan = collect($videos)->firstWhere('type', 'link');

        $this->assertSame('https://youtu.be/abc123XYZ', $tautan['src']);
        $this->assertNotNull($berkas, 'berkas video tidak tersimpan');
        Storage::disk('public')->assertExists($berkas['src']);
    }

    public function test_banyak_video_sekaligus_tersimpan_dan_tampil_di_kedua_halaman(): void
    {
        Storage::fake('public');
        $paket = $this->paket();

        $this->actingAs($this->admin())
            ->put(route('admin.packages.update', $paket), $this->dasar($paket) + [
                'video_links' => [
                    ['src' => 'https://youtu.be/aaaaaaaaaaa', 'title' => 'Hari 1'],
                    ['src' => 'https://www.youtube.com/watch?v=bbbbbbbbbbb', 'title' => 'Hari 2'],
                    ['src' => 'https://vimeo.com/76979871', 'title' => 'Hari 3'],
                ],
                'video_files' => [
                    UploadedFile::fake()->create('drone-pagi.mp4', 64, 'video/mp4'),
                    UploadedFile::fake()->create('drone-sore.mp4', 64, 'video/mp4'),
                ],
            ])
            ->assertRedirect();

        $videos = $paket->fresh()->videos;
        $this->assertCount(5, $videos, 'tidak semua video tersimpan');

        // Yang tersimpan harus benar-benar terender, bukan sekadar ada di DB.
        foreach ([route('tour.package.detail', $paket->slug), route('tour.package.detail.plain', $paket->slug)] as $url) {
            $html = $this->withSession(['locale' => 'id'])->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('youtube-nocookie.com/embed/aaaaaaaaaaa', $html, "video 1 hilang di {$url}");
            $this->assertStringContainsString('youtube-nocookie.com/embed/bbbbbbbbbbb', $html, "video 2 hilang di {$url}");
            $this->assertStringContainsString('player.vimeo.com/video/76979871', $html, "video 3 hilang di {$url}");
            $this->assertSame(2, substr_count($html, '<video src='), "dua video unggahan seharusnya punya pemutar sendiri di {$url}");
        }
    }

    public function test_menghapus_baris_tautan_terakhir_benar_benar_menghapusnya(): void
    {
        // Kalau form berhenti mengirim kunci video_links saat baris terakhir
        // dihapus, service tidak bisa membedakan "tidak ada perubahan" dari
        // "hapus semuanya" -- dan videonya akan hidup kembali tiap simpan.
        $paket = $this->paket(['videos' => [
            ['type' => 'link', 'src' => 'https://youtu.be/abc123XYZ', 'title' => 'Lama'],
        ]]);

        $this->actingAs($this->admin())
            ->put(route('admin.packages.update', $paket), $this->dasar($paket))
            ->assertRedirect();

        $this->assertSame([], $paket->fresh()->videos);
    }

    public function test_menghapus_video_terunggah_ikut_menghapus_berkasnya_dari_disk(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('packages/videos/lama.mp4', 'isi-video');

        $paket = $this->paket(['videos' => [
            ['type' => 'file', 'src' => 'packages/videos/lama.mp4', 'title' => 'lama.mp4'],
        ]]);

        $this->actingAs($this->admin())
            ->put(route('admin.packages.update', $paket), $this->dasar($paket) + [
                'remove_videos' => ['packages/videos/lama.mp4'],
            ])
            ->assertRedirect();

        $this->assertSame([], $paket->fresh()->videos);
        // Berkas 40 MB yang tidak lagi dirujuk siapa pun tetap memakan kuota
        // hosting sampai seseorang menyadarinya.
        Storage::disk('public')->assertMissing('packages/videos/lama.mp4');
    }

    public function test_brosur_baru_menggantikan_yang_lama_dan_berkas_lamanya_dibuang(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('packages/brochures/lama.pdf', '%PDF-lama');

        $paket = $this->paket(['brochure' => 'packages/brochures/lama.pdf']);

        $this->actingAs($this->admin())
            ->put(route('admin.packages.update', $paket), $this->dasar($paket) + [
                'brochure_file' => UploadedFile::fake()->create('baru.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $baru = $paket->fresh()->brochure;

        $this->assertNotSame('packages/brochures/lama.pdf', $baru);
        Storage::disk('public')->assertExists($baru);
        Storage::disk('public')->assertMissing('packages/brochures/lama.pdf');
    }

    public function test_berkas_video_tidak_menimpa_kolom_videos_dengan_objek_unggahan(): void
    {
        // 'videos' dan 'brochure' adalah kolom fillable. Kalau input berkasnya
        // dinamai sama, objek UploadedFile ikut masuk lewat fill() dan kolomnya
        // rusak tanpa error apa pun.
        Storage::fake('public');
        $paket = $this->paket();

        $this->actingAs($this->admin())
            ->put(route('admin.packages.update', $paket), $this->dasar($paket) + [
                'video_files' => [UploadedFile::fake()->create('a.mp4', 64, 'video/mp4')],
                'brochure_file' => UploadedFile::fake()->create('b.pdf', 64, 'application/pdf'),
            ])
            ->assertRedirect();

        $segar = $paket->fresh();

        $this->assertIsArray($segar->videos);
        $this->assertIsString($segar->brochure);
        $this->assertStringStartsWith('packages/', $segar->brochure);
    }
}
