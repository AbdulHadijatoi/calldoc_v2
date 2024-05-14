<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\TwilioService;
use Illuminate\Http\Client\Request;
use Twilio\Rest\Client;

class WhatsAppCallbackController extends Controller
{
    protected $twilioService;

    public function __construct() {
        $this->twilioService = new TwilioService();
    }

    public function sendMedia(){
        $phone = 766635027;
        // Check if the phone number starts with '+' or '212'
        if (!preg_match('/^\+?212/', $phone)) {
            // Prepend country code '+212' to the phone number
            $phone = '+212' . ltrim($phone, '0');
        }
        $url = url("prescription/audio_notes/audio.ogg");
        $setting = Setting::first();
        $sid = $setting->twilio_acc_id;
        $token = $setting->twilio_auth_token;

        try {
            $client = new Client($sid, $token);
            $client->messages->create(
                'whatsapp:' . $phone,
                [
                    'from' => 'whatsapp:' . $setting->twilio_phone_no,
                    'mediaUrl' => [$url]
                ]
            );
        } catch (\Throwable $th) {
            // Handle exception if required
            \Log::error($th->getMessage());
            return "failed";
        }
        return "success";
    }
}