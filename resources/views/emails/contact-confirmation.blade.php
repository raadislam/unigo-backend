@extends('emails.layout')

@section('content')
    <p style="margin:0 0 12px;font-size:11px;font-weight:bold;letter-spacing:2px;color:#d7192d;text-transform:uppercase;">
        Enquiry Confirmation
    </p>

    <h1 style="margin:0 0 20px;font-size:26px;line-height:34px;color:#082b4c;">
        We received your enquiry!
    </h1>

    <p style="font-size:15px;line-height:25px;color:#475569;">
        Dear {{ $enquiry->name }},
    </p>

    <p style="font-size:15px;line-height:25px;color:#475569;">
        Thank you for contacting UniGo. We have successfully received your enquiry.
        Our team will review your message and get back to you shortly.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
        style="margin:25px 0;background:#f3f6fa;border-radius:8px;">
        <tr>
            <td style="padding:18px 20px;">
                <p style="margin:0 0 7px;font-size:10px;font-weight:bold;letter-spacing:1px;color:#64748b;">
                    ENQUIRY REFERENCE
                </p>
                <p style="margin:0;font-size:18px;font-weight:bold;color:#082b4c;">
                    #{{ str_pad($enquiry->id, 6, '0', STR_PAD_LEFT) }}
                </p>
            </td>
        </tr>
    </table>

    <p style="font-size:14px;line-height:24px;color:#475569;">
        If you have additional questions, please contact our team.
    </p>

    <p style="margin-top:28px;font-size:14px;line-height:23px;color:#475569;">
        Best regards,<br>
        <strong style="color:#082b4c;">UniGo Team</strong>
    </p>
@endsection
