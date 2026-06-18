<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Station;
use App\Models\AssignLuggage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and admin user
        \App\Models\Role::updateOrCreate(
            ['role_name' => 'Manager'],
            ['status' => 0]
        );

        \App\Models\Role::updateOrCreate(
            ['role_name' => 'Driver'],
            ['status' => 0]
        );
    }

    /**
     * Test admin dashboard redirects to login if unauthenticated.
     */
    public function test_dashboard_redirects_unauthenticated_user(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/');
    }

    /**
     * Test admin dashboard successfully renders with expected data.
     */
    public function test_dashboard_renders_successfully_for_admin(): void
    {
        // Create an admin user (role_id = 0)
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => 0,
            'status' => 0,
        ]);

        // Create some mock entities
        Company::create([
            'company_name' => 'AeroFly',
            'company_code' => 'AF',
            'status' => 'active',
        ]);

        Station::create([
            'station_name' => 'John F. Kennedy',
            'station_code' => 'JFK',
            'city' => 'New York',
            'state' => 'New York',
            'country' => 'USA',
            'address' => 'Queens, NY 11430',
            'status' => 'active',
        ]);

        // Access dashboard authenticated with session
        $response = $this->withSession(['user_id' => $admin->id])
            ->get('/admin');

        $response->assertStatus(200);
        $response->assertViewHas('companiesCount', 1);
        $response->assertViewHas('stationsCount', 1);
        $response->assertViewHas('usersCount', 1);
        $response->assertViewHas('assignmentsCount', 0);
        $response->assertSee('Wings Control Center');
        $response->assertSee('Admin User');
    }
}
