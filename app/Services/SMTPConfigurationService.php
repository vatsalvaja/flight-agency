<?php

namespace App\Services;

use App\Models\SmtpSetting;
use App\Models\AssignLuggage;
use App\Mail\OrderAssignedMail;
use App\Mail\OrderPickedUpMail;
use App\Mail\OrderDeliveredMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SMTPConfigurationService
{
    /**
     * Get all SMTP settings from the database.
     */
    public function getDynamicSMTP(): array
    {
        return SmtpSetting::pluck('value', 'key')->toArray();
    }

    /**
     * Configure Laravel Mail dynamically using database SMTP settings.
     */
    public function configureMail(): bool
    {
        Log::info('SMTP Configuration: Attempting to load dynamic configuration.');

        $settings = $this->getDynamicSMTP();

        $required = [
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_password',
        ];

        foreach ($required as $key) {
            if (empty($settings[$key])) {
                Log::warning("SMTP Configuration: Missing required SMTP credential '{$key}'. Skipping dynamic mail configuration.");
                return false;
            }
        }

        // Deriving 'From Address' and 'From Name' dynamically
        $fromAddress = filter_var($settings['mail_username'], FILTER_VALIDATE_EMAIL) 
            ? $settings['mail_username'] 
            : config('mail.from.address');

        $fromName = null;
        try {
            $fromName = \App\Models\Setting::first()->application_name ?? config('mail.from.name');
        } catch (\Exception $e) {
            $fromName = config('mail.from.name');
        }

        // Set Laravel mail config at runtime
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $settings['mail_host'],
            'mail.mailers.smtp.port' => (int)$settings['mail_port'],
            'mail.mailers.smtp.username' => $settings['mail_username'],
            'mail.mailers.smtp.password' => $settings['mail_password'],
            'mail.mailers.smtp.encryption' => $settings['mail_encryption'] ?? null,
            'mail.from.address' => $fromAddress,
            'mail.from.name' => $fromName ?? config('app.name'),
        ]);

        if (!empty($settings['mail_charset'])) {
            config(['mail.charset' => $settings['mail_charset']]);
        }

        // Forget resolved mailer instances so they are re-instantiated with new config
        Mail::forgetMailers();

        Log::info('SMTP Configuration: Dynamic SMTP configuration loaded and set successfully.', [
            'host' => $settings['mail_host'],
            'port' => $settings['mail_port'],
            'username' => $settings['mail_username'],
            'encryption' => $settings['mail_encryption'] ?? 'none',
            'from_address' => $fromAddress,
        ]);

        return true;
    }

    /**
     * Send Order Assignment Email with error handling and logging.
     */
    public function sendOrderAssignmentEmail(AssignLuggage $assignment): void
    {
        $driver = $assignment->driver;
        if (!$driver || empty($driver->email)) {
            Log::warning('SMTP Configuration: Driver does not have a valid email. Skipping email sending.');
            return;
        }

        try {
            Log::info('SMTP Configuration: Starting email sending process.', [
                'recipient' => $driver->email,
                'assignment_id' => $assignment->id,
            ]);

            // Configure SMTP at runtime
            $configured = $this->configureMail();
            if (!$configured) {
                Log::warning('SMTP Configuration: Dynamic mail configuration failed. Skipping email sending.');
                return;
            }

            // Send Email (Synchronously)
            Mail::to($driver->email)->send(new OrderAssignedMail($assignment));

            Log::info('SMTP Configuration: Mail sent successfully to driver.', [
                'recipient' => $driver->email,
                'subject' => 'New Order Assigned',
                'assignment_id' => $assignment->id,
            ]);

        } catch (\Exception $e) {
            Log::error('SMTP Configuration: Mail sending failed.', [
                'recipient' => $driver->email,
                'assignment_id' => $assignment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Send Order Picked Up Email to the manager who assigned the order.
     */
    public function sendOrderPickedUpEmail(AssignLuggage $assignment): void
    {
        $creator = $assignment->creator;
        if (!$creator || empty($creator->email)) {
            Log::warning('SMTP Configuration: Creator manager does not have a valid email. Skipping email sending.');
            return;
        }

        try {
            Log::info('SMTP Configuration: Starting email sending process (Picked Up).', [
                'recipient' => $creator->email,
                'assignment_id' => $assignment->id,
            ]);

            // Configure SMTP at runtime
            $configured = $this->configureMail();
            if (!$configured) {
                Log::warning('SMTP Configuration: Dynamic mail configuration failed. Skipping email sending.');
                return;
            }

            // Send Email (Synchronously)
            Mail::to($creator->email)->send(new OrderPickedUpMail($assignment));

            Log::info('SMTP Configuration: Mail sent successfully to manager (Picked Up).', [
                'recipient' => $creator->email,
                'subject' => 'Order Picked Up',
                'assignment_id' => $assignment->id,
            ]);

        } catch (\Exception $e) {
            Log::error('SMTP Configuration: Mail sending failed (Picked Up).', [
                'recipient' => $creator->email,
                'assignment_id' => $assignment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Send Order Delivered Email to the manager who assigned the order.
     */
    public function sendOrderDeliveredEmail(AssignLuggage $assignment): void
    {
        $creator = $assignment->creator;
        if (!$creator || empty($creator->email)) {
            Log::warning('SMTP Configuration: Creator manager does not have a valid email. Skipping email sending.');
            return;
        }

        try {
            Log::info('SMTP Configuration: Starting email sending process (Delivered).', [
                'recipient' => $creator->email,
                'assignment_id' => $assignment->id,
            ]);

            // Configure SMTP at runtime
            $configured = $this->configureMail();
            if (!$configured) {
                Log::warning('SMTP Configuration: Dynamic mail configuration failed. Skipping email sending.');
                return;
            }

            // Send Email (Synchronously)
            Mail::to($creator->email)->send(new OrderDeliveredMail($assignment));

            Log::info('SMTP Configuration: Mail sent successfully to manager (Delivered).', [
                'recipient' => $creator->email,
                'subject' => 'Order Delivered',
                'assignment_id' => $assignment->id,
            ]);

        } catch (\Exception $e) {
            Log::error('SMTP Configuration: Mail sending failed (Delivered).', [
                'recipient' => $creator->email,
                'assignment_id' => $assignment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
