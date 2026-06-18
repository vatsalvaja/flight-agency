<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Station;
use App\Models\Role;
use App\Models\AssignLuggage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $managerRole;
    protected $driverRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->managerRole = Role::updateOrCreate(
            ['role_name' => 'Manager'],
            ['status' => 0]
        );

        $this->driverRole = Role::updateOrCreate(
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

    /**
     * Test manager dashboard successfully renders with manager-scoped data.
     */
    public function test_dashboard_renders_successfully_for_manager(): void
    {
        // Create manager user
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => $this->managerRole->id,
            'status' => 0,
        ]);

        $driver = User::create([
            'name' => 'Driver User',
            'email' => 'driver@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => $this->driverRole->id,
            'status' => 0,
        ]);

        $company = Company::create([
            'company_name' => 'AeroFly',
            'company_code' => 'AF',
            'status' => 'active',
        ]);

        $station = Station::create([
            'station_name' => 'John F. Kennedy',
            'station_code' => 'JFK',
            'city' => 'New York',
            'state' => 'New York',
            'country' => 'USA',
            'address' => 'Queens, NY 11430',
            'status' => 'active',
        ]);

        // Create assignment created by the manager
        AssignLuggage::create([
            'company_id' => $company->id,
            'station_id' => $station->id,
            'driver_id' => $driver->id,
            'pickup_location' => 'Pickup Loc',
            'drop_location' => 'Drop Loc',
            'expected_delivery_date' => now()->addDays(2),
            'status' => 'Pickup',
            'created_by' => $manager->id,
        ]);

        // Access dashboard authenticated as manager
        $response = $this->withSession(['user_id' => $manager->id])
            ->get('/admin');

        $response->assertStatus(200);
        $response->assertViewHas('isManager', true);
        $response->assertViewHas('assignmentsCount', 1);
        $response->assertSee('Manager Operations Dashboard');
        $response->assertSee('Manager User');
    }

    /**
     * Test driver dashboard successfully renders with driver-scoped data.
     */
    public function test_dashboard_renders_successfully_for_driver(): void
    {
        // Create driver user
        $driver = User::create([
            'name' => 'Driver User',
            'email' => 'driver@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => $this->driverRole->id,
            'status' => 0,
        ]);

        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => $this->managerRole->id,
            'status' => 0,
        ]);

        $company = Company::create([
            'company_name' => 'AeroFly',
            'company_code' => 'AF',
            'status' => 'active',
        ]);

        $station = Station::create([
            'station_name' => 'John F. Kennedy',
            'station_code' => 'JFK',
            'city' => 'New York',
            'state' => 'New York',
            'country' => 'USA',
            'address' => 'Queens, NY 11430',
            'status' => 'active',
        ]);

        // Create assignment assigned to driver
        AssignLuggage::create([
            'company_id' => $company->id,
            'station_id' => $station->id,
            'driver_id' => $driver->id,
            'pickup_location' => 'Pickup Loc',
            'drop_location' => 'Drop Loc',
            'expected_delivery_date' => now()->addDays(2),
            'status' => 'In Progress',
            'created_by' => $manager->id,
        ]);

        // Access dashboard authenticated as driver
        $response = $this->withSession(['user_id' => $driver->id])
            ->get('/admin');

        $response->assertStatus(200);
        $response->assertViewHas('isDriver', true);
        $response->assertViewHas('assignmentsCount', 1);
        $response->assertSee('Driver Logistics Dashboard');
        $response->assertSee('Driver User');
    }
}
