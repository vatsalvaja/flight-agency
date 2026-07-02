<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\SmtpSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SMTPSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected $managerRole;
    protected $driverRole;
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->managerRole = Role::updateOrCreate(['role_name' => 'Manager'], ['status' => 0]);
        $this->driverRole = Role::updateOrCreate(['role_name' => 'Driver'], ['status' => 0]);

        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => 0,
            'status' => 0,
        ]);
    }

    /**
     * Test SMTP Settings page is accessible by Admin.
     */
    public function test_smtp_settings_page_is_accessible_by_admin(): void
    {
        $response = $this->withSession(['user_id' => $this->adminUser->id])
            ->get('/admin/settings/smtp');

        $response->assertStatus(200);
        $response->assertSee('SMTP Mail Configuration');
    }

    /**
     * Test SMTP Settings page is protected from non-admins (e.g., Drivers).
     */
    public function test_smtp_settings_page_is_denied_for_drivers(): void
    {
        $driver = User::create([
            'name' => 'Driver User',
            'email' => 'driver@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => $this->driverRole->id,
            'status' => 0,
        ]);

        $response = $this->withSession(['user_id' => $driver->id])
            ->get('/admin/settings/smtp');

        // Middleware redirects unauthorized admin access to dashboard
        $response->assertRedirect('/admin');
    }

    /**
     * Test SMTP settings save successfully and validate required inputs.
     */
    public function test_smtp_settings_save_and_validation(): void
    {
        // 1. Invalid payload (missing required host, user, port, etc.)
        $response = $this->withSession(['user_id' => $this->adminUser->id])
            ->put('/admin/settings/smtp', [
                'mail_host' => '',
                'mail_port' => 'abc', // non-numeric
            ]);

        $response->assertSessionHasErrors(['mail_host', 'mail_port', 'mail_username', 'mail_password']);

        // 2. Valid payload
        $response = $this->withSession(['user_id' => $this->adminUser->id])
            ->put('/admin/settings/smtp', [
                'mail_host' => 'smtp.mailtrap.io',
                'mail_port' => '2525',
                'mail_username' => 'testuser',
                'mail_password' => 'testpass',
                'mail_encryption' => 'tls',
                'mail_charset' => 'UTF-8',
            ]);

        $response->assertRedirect('/admin/settings/smtp');
        $response->assertSessionHas('success', 'SMTP settings updated successfully.');

        // Verify it was saved as individual key-value rows
        $this->assertDatabaseHas('smtp_settings', ['key' => 'mail_host', 'value' => 'smtp.mailtrap.io']);
        $this->assertDatabaseHas('smtp_settings', ['key' => 'mail_port', 'value' => '2525']);
        $this->assertDatabaseCount('smtp_settings', 6);

        // 3. Update existing setting
        $response = $this->withSession(['user_id' => $this->adminUser->id])
            ->put('/admin/settings/smtp', [
                'mail_host' => 'smtp.gmail.com',
                'mail_port' => '587',
                'mail_username' => 'gmailuser',
                'mail_password' => 'gmailpass',
                'mail_encryption' => 'tls',
                'mail_charset' => 'UTF-8',
            ]);

        $this->assertDatabaseHas('smtp_settings', ['key' => 'mail_host', 'value' => 'smtp.gmail.com']);
        $this->assertDatabaseHas('smtp_settings', ['key' => 'mail_port', 'value' => '587']);
        // Verify we don't have duplicate rows (count remains 6)
        $this->assertDatabaseCount('smtp_settings', 6);
    }
}
