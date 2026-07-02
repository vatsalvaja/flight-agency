<?php

namespace App\Mail;

use App\Models\AssignLuggage;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderAssignedMail extends Mailable
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
        $appName = $this->appSettings->application_name ?? 'Wings & Wheels';
        return new Envelope(
            subject: 'New Order Assigned - ' . $appName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-assigned',
        );
    }
}
