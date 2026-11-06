<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class BookingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_flow_creates_booking_and_returns_whatsapp()
    {
        // Disable throttle middleware to avoid rate limiting in tests
        $this->withoutMiddleware(ThrottleRequests::class);
        // Prepare settings and package
        Setting::create(['key' => 'general', 'value' => ['contact_whatsapp' => '6281260460461']]);

        $package = Package::create([
            'slug' => 'integration-package',
            'name' => 'Integration Package',
            'shortDescription' => 'Short desc',
            'description' => 'Full description',
            'images' => json_encode([]),
            'includes' => json_encode([]),
            'excludes' => json_encode([]),
            'pricingDetails' => json_encode([]),
            'itinerary' => json_encode([]),
            'translations' => json_encode([]),
            'price' => 500000,
            'duration' => '2 Hari',
            'status' => 'active',
        ]);

        $token = 'test-token';

        $response = $this->withSession(['_token' => $token])
            ->post(route('tour.booking.submit'), [
                '_token' => $token,
                'packageId' => $package->id,
                'customerName' => 'Budi Test',
                'customerEmail' => 'budi@test.local',
                'customerPhone' => '08123456789',
                'startDate' => date('Y-m-d', strtotime('+1 day')),
                'pax' => 2,
                'notes' => 'Test booking via integration test',
                // Persetujuan S&K + Privasi jadi wajib sejak commit a207940
                // (UU PDP 27/2022 & PDPA). Tanpa baris ini tes mengirim form
                // yang memang seharusnya ditolak, lalu mengeluh ditolak.
                'terms' => 'on',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $response->assertSessionHas('whatsappUrl');

        $this->assertDatabaseHas('bookings', [
            'customerName' => 'Budi Test',
            'customerEmail' => 'budi@test.local',
            'status' => 'pending',
        ]);
    }

    public function test_package_pricing_details_can_be_saved_via_tour_service()
    {
        $tourService = app(\App\Services\TourService::class);

        $data = [
            'name' => 'Custom Pricing Package',
            'description' => 'Test description',
            'duration' => '3 Hari',
            'price' => 1500000,
            'status' => 'active',
            'pricingDetails' => [
                'additional_services' => [
                    ['name' => 'Custom Jet', 'icon' => 'flight', 'price' => 130000000],
                    ['name' => 'Custom Guide', 'icon' => 'person', 'price' => 6000000],
                ],
            ],
        ];

        $package = $tourService->savePackage($data);

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'name' => 'Custom Pricing Package',
        ]);

        $this->assertEquals('Custom Jet', $package->pricingDetails['additional_services'][0]['name']);
        $this->assertEquals(130000000, $package->pricingDetails['additional_services'][0]['price']);
        $this->assertEquals('Custom Guide', $package->pricingDetails['additional_services'][1]['name']);
        $this->assertEquals(6000000, $package->pricingDetails['additional_services'][1]['price']);
    }

    public function test_cleared_additional_services_are_saved_as_empty_array()
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        $admin = \App\Models\User::factory()->create(['role' => 'superadmin']);

        $package = Package::create([
            'slug' => 'original-package',
            'name' => 'Original Package',
            'description' => 'Original description',
            'duration' => '3 Hari',
            'images' => [],
            'includes' => [],
            'excludes' => [],
            'price' => 1500000,
            'status' => 'active',
            'pricingDetails' => [
                'additional_services' => [
                    ['name' => 'Original Jet', 'icon' => 'flight', 'price' => 120000000],
                ],
            ],
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.packages.update', $package), [
            'name' => 'Updated Package',
            'description' => 'Original description',
            'duration' => '3 Hari',
            'price' => 1500000,
            'status' => 'active',
            // No pricingDetails submitted, simulating deletion of all additional services in form
        ]);

        $response->assertRedirect();
        $package->refresh();

        $this->assertEquals([], $package->pricingDetails['additional_services']);
    }
}
