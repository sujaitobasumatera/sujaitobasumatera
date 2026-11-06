<?php

namespace Tests\Feature;

use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LocationSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_seeder_mengisi_semua_provinsi_dan_kabupaten()
    {
        $this->seed(LocationSeeder::class);

        $this->assertSame(38, DB::table('provinces')->count());
        $this->assertSame(514, DB::table('regencies')->count());
    }

    /** @test */
    public function test_seeder_idempotent_tidak_menggandakan()
    {
        $this->seed(LocationSeeder::class);
        $this->seed(LocationSeeder::class);

        $this->assertSame(38, DB::table('provinces')->count());
        $this->assertSame(514, DB::table('regencies')->count());
    }

    /** @test */
    public function test_kategori_kurasi_sumut_dipertahankan()
    {
        $this->seed(LocationSeeder::class);

        $sumutId = DB::table('provinces')->where('name', 'Sumatera Utara')->value('id');
        $samosir = DB::table('regencies')
            ->where('province_id', $sumutId)
            ->where('name', 'Kabupaten Samosir')
            ->first();

        $this->assertNotNull($samosir);
        $this->assertSame('Destinasi Prioritas', $samosir->category);
    }

    /** @test */
    public function test_nama_provinsi_selaras_dengan_aplikasi()
    {
        $this->seed(LocationSeeder::class);

        $this->assertTrue(DB::table('provinces')->where('name', 'DKI Jakarta')->exists());
        $this->assertTrue(DB::table('provinces')->where('name', 'DI Yogyakarta')->exists());
        $this->assertFalse(DB::table('provinces')->where('name', 'Daerah Khusus Ibukota Jakarta')->exists());
    }
}
