<?php

namespace App\Listeners;

use App\Events\OtpRequested;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SendOtpNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OtpRequested $event)
    {
        $otpVerification = $event->otpVerification;
        $user = $otpVerification->user;

        if ($user->phone == '967777777777') {
            return;
        }

        // Here you can integrate your SMS gateway to send the OTP.
        // For demonstration, we log the OTP.
        Log::info("Sending OTP {$otpVerification->otp} to phone number {$user->phone}");
        try {
            $request = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'authorization' => 'Bearer ' . env('WHATSAPP_API_KEY'),
            ])->post(
                    'https://graph.facebook.com/v18.0/' . env('WHATSAPP_PHONE_NUMBER') . '/messages',
                    [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => "$user->phone",
                        "type" => "template",
                        "template" => [
                            "name" => env('WHATSAPP_TEMPLATE'),
                            "language" => [
                                "code" => "ar"
                            ],
                            "components" => [
                                [
                                    "type" => "body",
                                    "parameters" => [
                                        [
                                            "type" => "text",
                                            "text" => "$otpVerification->otp"
                                        ]
                                    ]
                                ],
                                [
                                    "type" => "button",
                                    "sub_type" => "url",
                                    "index" => "0",
                                    "parameters" => [
                                        [
                                            "type" => "text",
                                            "text" => "$otpVerification->otp"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                );
            return $request;
        } catch (Exception $e) {
            Log::error('An error occurred while sending WhatsApp message: ' . $e->getMessage());
        }

        try {
            $message = urlencode("<#>" . "\n" . "رمز تطبيق شي باي هو $otpVerification->otp");

            $request = Http::post(
                env('SMS_LINK') .
                "&mobileNo=" . $user->phone . "&text=" . $message . "&coding=2"
            );
        } catch (Exception $e) {
            Log::error('An error occurred while sending SMS: ' . $e->getMessage());
        }
    }
}
