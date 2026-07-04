<?php

namespace App\Mail;

use App\Models\AssignLuggage;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDeliveredMail extends Mailable
{
    use Queueable, SerializesModels;

    public $assignment;
    public $appSettings;

    /**
     * Create a new message instance.
     */
    public function __construct(AssignLuggage $assignment)
    {
        $this->assignment = $assignment;
        $this->appSettings = Setting::first() ?? new Setting([
            'application_name' => 'Wings & Wheels',
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Dynamically configure SMTP settings before sending
        app(\App\Services\SMTPConfigurationService::class)->configureMail();

        $appName = $this->appSettings->application_name ?? 'Wings & Wheels';
        $orderNum = str_pad($this->assignment->id, 5, '0', STR_PAD_LEFT);
        return new Envelope(
            subject: 'Order Delivered - #ORD-' . $orderNum . ' - ' . $appName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-delivered',
        );
    }
}
