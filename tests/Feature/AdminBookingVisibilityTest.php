<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class AdminBookingVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_shows_up_in_admin_bookings_list()
    {
        // Prepare settings and package
        Setting::create(['key' => 'general', 'value' => ['whatsapp' => '6281260460461']]);

        $package = Package::create([
            'slug' => 'admin-vis-package',
            'name' => 'Admin Vis Package',
            'shortDescription' => 'Short',
            'description' => 'Desc',
            'images' => json_encode([]),
            'includes' => json_encode([]),
            'excludes' => json_encode([]),
            'pricingDetails' => json_encode([]),
            'itinerary' => json_encode([]),
            'translations' => json_encode([]),
            'price' => 100000,
            'duration' => '1 Hari',
            'status' => 'active',
        ]);

        // Disable throttle to avoid rate limit
        $this->withoutMiddleware(ThrottleRequests::class);

        $token = 'test-token';

        $response = $this->withSession(['_token' => $token])
            ->post(route('tour.booking.submit'), [
                '_token' => $token,
                'packageId' => $package->id,
                'customerName' => 'Siti Tester',
                'customerEmail' => 'siti@test.local',
                'customerPhone' => '081122334455',
                'startDate' => date('Y-m-d', strtotime('+2 days')),
                'pax' => 3,
                // Wajib sejak commit a207940 — lihat catatan di BookingIntegrationTest.
                'terms' => 'on',
            ]);

        $response->assertStatus(302);

        // Create admin user and access admin bookings
        $admin = User::factory()->create(['role' => 'superadmin']);

        $adminResp = $this->actingAs($admin)->get('/admin/bookings');

        $adminResp->assertStatus(200);
        $adminResp->assertViewHas('bookings');

        $bookings = $adminResp->viewData('bookings');
        $this->assertGreaterThanOrEqual(1, $bookings->total());
    }
}
