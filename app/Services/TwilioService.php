<?php

namespace App\Services;

use App\Models\Setting;
use Twilio\Rest\Client;

class TwilioService {
    
    public function sendMediaFile($phone, $mediaUrl)
    {
        // Check if the phone number starts with '+' or '212'
        if (!preg_match('/^\+?212/', $phone)) {
            // Prepend country code '+212' to the phone number
            $phone = '+212' . ltrim($phone, '0');
        }

        $setting = Setting::first();
        $sid = $setting->twilio_acc_id;
        $token = $setting->twilio_auth_token;

        try {
            $client = new Client($sid, $token);
            $client->messages->create(
                'whatsapp:' . $phone,
                [
                    'from' => 'whatsapp:' . $setting->twilio_phone_no,
                    'body' => $mediaUrl
                ]
            );
        } catch (\Throwable $th) {
            // Handle exception if required
            \Log::error($th->getMessage());
        }
    }
    
    public function sendWhatsAppNotification($phone, $message)
    {
        // Check if the phone number starts with '+' or '212'
        if (!preg_match('/^\+?212/', $phone)) {
            // Prepend country code '+212' to the phone number
            $phone = '+212' . ltrim($phone, '0');
        }

        $setting = Setting::first();
        $sid = $setting->twilio_acc_id;
        $token = $setting->twilio_auth_token;

        try {
            $client = new Client($sid, $token);
            $client->messages->create(
                'whatsapp:' . $phone,
                [
                    'from' => 'whatsapp:' . $setting->twilio_phone_no,
                    'body' => $message
                ]
            );
        } catch (\Throwable $th) {
            // Handle exception if required
            \Log::error($th->getMessage());
        }
    }
  
    public function sendContentTemplate($phone, $contentSid, $key_values_array = []) {
        if (!preg_match('/^\+?212/', $phone)) {
            $phone = '+212' . ltrim($phone, '0');
        }

        $setting = Setting::first();
        $sid = $setting->twilio_acc_id;
        $token = $setting->twilio_auth_token;

        try {
            $client = new Client($sid, $token);
            $client->messages->create('whatsapp:' . $phone,
                [
                    "contentSid" => $contentSid,
                    'from' => 'whatsapp:' . $setting->twilio_phone_no,
                    "contentVariables" => json_encode($key_values_array),
                    "messagingServiceSid" => "MGccf8824374d20217e42121c760a3f955"
                ]
            );
            
        } catch (\Throwable $th) {
            \Log::error($th->getMessage());
        }
    }

}