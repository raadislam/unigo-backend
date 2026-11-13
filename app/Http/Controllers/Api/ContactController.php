<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactEnquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $enquiry = ContactEnquiry::create($validated);

        $notificationSent = false;
        $confirmationSent = false;

        try {
            Mail::send(
                'emails.contact-notification',
                [
                    'enquiry' => $enquiry,
                    'subject' => "New Website Enquiry #{$enquiry->id} - UniGo",
                ],
                function ($mail) use ($enquiry) {
                    $mail->to(config('mail.contact_recipient'))
                        ->subject("New Website Enquiry #{$enquiry->id} - UniGo");

                    if ($enquiry->email) {
                        $mail->replyTo($enquiry->email, $enquiry->name);
                    }
                }
            );

            $notificationSent = true;
        } catch (Throwable $exception) {
            Log::error('Contact enquiry notification failed', [
                'enquiry_id' => $enquiry->id,
                'error' => $exception->getMessage(),
            ]);
        }

        if ($enquiry->email) {
            try {
                Mail::send(
                    'emails.contact-confirmation',
                    [
                        'enquiry' => $enquiry,
                        'subject' => 'We received your enquiry - UniGo',
                    ],
                    function ($mail) use ($enquiry) {
                        $mail->to($enquiry->email)
                            ->replyTo(
                                'contact@unigo-bd.com',
                                'UniGo Team'
                            )
                            ->subject('We received your enquiry - UniGo');
                    }
                );

                $confirmationSent = true;
            } catch (Throwable $exception) {
                Log::error('Contact confirmation email failed', [
                    'enquiry_id' => $enquiry->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Your enquiry has been received successfully.',
            'data' => [
                'id' => $enquiry->id,
                'notification_sent' => $notificationSent,
                'confirmation_sent' => $confirmationSent,
            ],
        ], 201);
    }
}
