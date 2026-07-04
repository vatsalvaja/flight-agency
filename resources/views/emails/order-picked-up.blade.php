<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Picked Up</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
                padding: 10px !important;
            }
            .content-padding {
                padding: 20px !important;
            }
            .detail-row {
                display: block !important;
                width: 100% !important;
                margin-bottom: 15px !important;
            }
            .detail-label {
                display: block !important;
                margin-bottom: 2px !important;
            }
            .image-col {
                width: 100% !important;
                display: block !important;
                margin-bottom: 15px !important;
            }
            .image-box img {
                height: 160px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; color: #334155; -webkit-font-smoothing: antialiased;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; padding: 30px 0;">
        <tr>
            <td align="center">
                <table class="container" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03); overflow: hidden; border: 1px solid #e2e8f0;">
                    <!-- Brand Header with Premium Gradient -->
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 35px 40px; color: #ffffff; text-align: center;">
                            @if(isset($appSettings->application_logo) && !empty($appSettings->application_logo) && file_exists(public_path($appSettings->application_logo)))
                                <img src="{{ $message->embed(public_path($appSettings->application_logo)) }}" alt="{{ $appSettings->application_name }}" style="max-height: 55px; margin: 0 auto 12px auto; display: block;">
                            @else
                                @if(isset($appSettings->application_logo) && !empty($appSettings->application_logo))
                                    <img src="{{ url($appSettings->application_logo) }}" alt="{{ $appSettings->application_name }}" style="max-height: 55px; margin: 0 auto 12px auto; display: block;">
                                @endif
                            @endif
                            <h1 style="margin: 0 auto; font-size: 24px; font-weight: 700; letter-spacing: -0.5px; color: #ffffff; text-align: center;">{{ $appSettings->application_name ?? 'Wings & Wheels' }}</h1>
                            <p style="margin: 5px auto 0 auto; font-size: 13px; color: #94a3b8; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; text-align: center;">Logistics Management</p>
                        </td>
                    </tr>

                    <!-- Notification Accent -->
                    <tr>
                        <td class="content-padding" style="padding: 40px 40px 0 40px;">
                            <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px 20px; border-radius: 8px;">
                                <span style="color: #78350f; font-size: 14px; font-weight: 600; display: block;">Notification Status: Picked Up</span>
                                <span style="color: #92400e; font-size: 13px; display: block; margin-top: 2px;">The assigned driver has successfully picked up the order.</span>
                            </div>
                        </td>
                    </tr>

                    <!-- Main Message -->
                    <tr>
                        <td class="content-padding" style="padding: 30px 40px 20px 40px;">
                            <h2 style="margin: 0 0 10px 0; color: #0f172a; font-size: 20px; font-weight: 700; letter-spacing: -0.3px;">Hello, {{ $assignment->creator->name ?? 'Manager' }}!</h2>
                            <p style="margin: 0; font-size: 15px; line-height: 1.6; color: #475569;">We would like to inform you that your assigned order has been successfully picked up by the driver and is now in transit.</p>
                        </td>
                    </tr>

                    <!-- Delivery details card -->
                    <tr>
                        <td class="content-padding" style="padding: 10px 40px 20px 40px;">
                            <div style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 25px;">
                                <h3 style="margin: 0 0 20px 0; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600;">Order Details</h3>
                                
                                <!-- Timeline flow (Pickup -> Drop) -->
                                <div style="margin-bottom: 25px; border-left: 2px dashed #cbd5e1; padding-left: 20px; position: relative;">
                                    <div style="margin-bottom: 20px;">
                                        <span style="color: #64748b; font-size: 12px; font-weight: 600; text-transform: uppercase; display: block;">Pickup Location</span>
                                        <strong style="color: #0f172a; font-size: 15px; display: block; margin-top: 2px;">{{ $assignment->pickup_location }}</strong>
                                    </div>
                                    <div>
                                        <span style="color: #64748b; font-size: 12px; font-weight: 600; text-transform: uppercase; display: block;">Drop Location (Destination)</span>
                                        <strong style="color: #0f172a; font-size: 15px; display: block; margin-top: 2px;">{{ $assignment->drop_location }}</strong>
                                    </div>
                                </div>

                                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">

                                <!-- Other Details Grid -->
                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td class="detail-row" width="50%" style="padding-bottom: 15px; vertical-align: top;">
                                            <span class="detail-label" style="font-size: 12px; color: #94a3b8; font-weight: 600; text-transform: uppercase; display: block;">Order ID</span>
                                            <span style="font-size: 14px; color: #0f172a; font-weight: 600; display: block; margin-top: 2px;">#ORD-{{ str_pad($assignment->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        </td>
                                        <td class="detail-row" width="50%" style="padding-bottom: 15px; vertical-align: top;">
                                            <span class="detail-label" style="font-size: 12px; color: #94a3b8; font-weight: 600; text-transform: uppercase; display: block;">Customer Name</span>
                                            <span style="font-size: 14px; color: #0f172a; font-weight: 600; display: block; margin-top: 2px;">
                                                {{ $assignment->company->company_name ?? 'N/A' }}
                                                @if(isset($assignment->company->contact_person) && !empty($assignment->company->contact_person))
                                                    ({{ $assignment->company->contact_person }})
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="detail-row" style="padding-bottom: 15px; vertical-align: top;">
                                            <span class="detail-label" style="font-size: 12px; color: #94a3b8; font-weight: 600; text-transform: uppercase; display: block;">Driver's Name</span>
                                            <span style="font-size: 14px; color: #0f172a; font-weight: 500; display: block; margin-top: 2px;">{{ $assignment->driver->name ?? 'N/A' }}</span>
                                        </td>
                                        <td class="detail-row" style="padding-bottom: 15px; vertical-align: top;">
                                            <span class="detail-label" style="font-size: 12px; color: #94a3b8; font-weight: 600; text-transform: uppercase; display: block;">Pickup Date & Time</span>
                                            <span style="font-size: 14px; color: #0f172a; font-weight: 500; display: block; margin-top: 2px;">
                                                {{ $assignment->updated_at ? $assignment->updated_at->format('M d, Y h:i A') : \Carbon\Carbon::now()->format('M d, Y h:i A') }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="detail-row" style="vertical-align: top;">
                                            <span class="detail-label" style="font-size: 12px; color: #94a3b8; font-weight: 600; text-transform: uppercase; display: block;">Driver Contact</span>
                                            <span style="font-size: 14px; color: #0f172a; font-weight: 500; display: block; margin-top: 2px;">{{ $assignment->driver->phone ?? 'N/A' }}</span>
                                        </td>
                                        <td class="detail-row" style="vertical-align: top;">
                                            <span class="detail-label" style="font-size: 12px; color: #94a3b8; font-weight: 600; text-transform: uppercase; display: block;">Status Badge</span>
                                            <span style="font-size: 12px; background-color: #fef3c7; color: #d97706; font-weight: 600; padding: 4px 10px; border-radius: 6px; display: inline-block; margin-top: 2px; text-transform: uppercase;">Picked Up</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    <!-- Special Notes Section -->
                    @if($assignment->notes)
                    <tr>
                        <td class="content-padding" style="padding: 10px 40px 10px 40px;">
                            <div style="background-color: #fefcf0; border: 1px solid #fef3c7; border-radius: 8px; padding: 15px 20px;">
                                <span style="font-size: 12px; color: #b45309; font-weight: 600; text-transform: uppercase; display: block;">Special Instructions</span>
                                <p style="margin: 5px 0 0 0; font-size: 14px; color: #78350f; font-style: italic; line-height: 1.5;">&ldquo;{{ $assignment->notes }}&rdquo;</p>
                            </div>
                        </td>
                    </tr>
                    @endif

                    <!-- Footer (Dark Slate) -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 40px; color: #94a3b8; font-size: 13px; line-height: 1.6; text-align: center;">
                            <p style="margin: 0;">&copy; {{ date('Y') }} {{ $appSettings->application_name ?? 'Wings & Wheels' }}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
