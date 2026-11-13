
@extends('emails.layout')

@section('content')
    <p style="margin:0 0 12px;font-size:11px;font-weight:bold;letter-spacing:2px;color:#d7192d;text-transform:uppercase;">
        Website Contact Enquiry
    </p>

    <h1 style="margin:0 0 20px;font-size:25px;line-height:33px;color:#082b4c;">
        New enquiry received
    </h1>

    <p style="font-size:14px;line-height:24px;color:#475569;">
        A visitor has submitted a new enquiry through the UniGo website.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;margin:20px 0;">
        <tr>
            <td style="padding:12px;border-bottom:1px solid #e8edf3;font-size:13px;color:#64748b;width:125px;">Reference</td>
            <td style="padding:12px;border-bottom:1px solid #e8edf3;font-size:13px;font-weight:bold;color:#082b4c;">#{{ str_pad($enquiry->id, 6, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td style="padding:12px;border-bottom:1px solid #e8edf3;font-size:13px;color:#64748b;">Full Name</td>
            <td style="padding:12px;border-bottom:1px solid #e8edf3;font-size:13px;color:#082b4c;">{{ $enquiry->name }}</td>
        </tr>
        <tr>
            <td style="padding:12px;border-bottom:1px solid #e8edf3;font-size:13px;color:#64748b;">Phone</td>
            <td style="padding:12px;border-bottom:1px solid #e8edf3;font-size:13px;color:#082b4c;">{{ $enquiry->phone }}</td>
        </tr>
        <tr>
            <td style="padding:12px;border-bottom:1px solid #e8edf3;font-size:13px;color:#64748b;">Email</td>
            <td style="padding:12px;border-bottom:1px solid #e8edf3;font-size:13px;color:#082b4c;">{{ $enquiry->email ?: 'Not provided' }}</td>
        </tr>
    </table>

    <p style="font-size:12px;font-weight:bold;color:#082b4c;">MESSAGE</p>

    <div style="padding:18px;background:#f3f6fa;border-left:3px solid #d7192d;border-radius:5px;font-size:14px;line-height:24px;color:#475569;white-space:pre-wrap;">{{ $enquiry->message }}</div>

    <p style="margin-top:25px;font-size:13px;color:#64748b;">
        Reply to this email to respond directly to the visitor, if an email address was provided.
    </p>
@endsection
