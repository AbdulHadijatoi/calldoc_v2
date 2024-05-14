<?php

namespace App\AppointmentReminders;

use App\Models\Appointment;
use App\Services\TwilioService;
use Illuminate\Support\Facades\Log;

class AppointmentReminder
{
    public $appointments24Hours;
    public $appointments10Days;
    protected $twilioService;
    /**
     * Construct a new AppointmentReminder
     *
     * @param Illuminate\Support\Collection $twilioClient The client to use to query the API
     */
    function __construct()
    {

        $this->appointments24Hours = Appointment::appointmentsIn24Hours()->get();
        $this->appointments10Days = Appointment::appointmentsWithin10Days()->get();
        $this->twilioService = new TwilioService();
    }

    /**
     * Send reminders for each appointment
     *
     * @return void
     */
    public function sendReminders() {
        $this->appointments24Hours->each(
            function ($appointment) {
                $this->_send24HoursNotification($appointment);
                $appointment->notification_24_hours_sent = 1;
                $appointment->save();
            }
        );
        
        $this->appointments10Days->each(
            function ($appointment) {
                $this->_send10daysNotification($appointment);
                $appointment->notification_10_days_sent = 1;
                $appointment->save();
            }
        );
    }

    /**
     * Sends a single message using the app's global configuration
     *
     * @param string $number  The number to message
     * @param string $content The content of the message
     *
     * @return void
     */
    private function _send24HoursNotification($appointment)
    {
        Log::info('AppointmentReminder:_send24HoursNotification() sending appointment reminder',[$appointment]); // Log message
        // TOMORROW APPOINTMENT TO PATIENT
        $user = $appointment->user;
        $doctor = $appointment->doctor;
        $hospital = $appointment->hospital;
        $hospitalAddress = $hospital?$hospital->address??null:null;
        if($hospitalAddress && $user){
            $lat = $hospital->lat;
            $long = $hospital->lng;
            $google_map_url = "https://www.google.com/maps?q=$lat,$long";
            $this->twilioService->sendContentTemplate($user->phone,'HX5808a62b256946c4c514a6c061bf969a',[
                "1" => $user->name,
                "2" => $appointment->date,
                "3" => $appointment->time,
                "4" => $doctor->name,
                "5" => $hospitalAddress,
                "6" => $doctor->user->phone_code . $doctor->user->phone,
                "7" => $google_map_url,
            ]);
        }
    }
    
    private function _send10daysNotification($appointment)
    {
        Log::info('AppointmentReminder:_send10DaysNotification() sending appointment reminder',[$appointment]); // Log message
        // TOMORROW APPOINTMENT TO PATIENT
        $user = $appointment->user;
        $doctor = $appointment->doctor;
        $hospital = $appointment->hospital;
        $hospitalAddress = $hospital?$hospital->address??null:null;
        if($hospitalAddress && $user){
            $this->twilioService->sendContentTemplate($user->phone,'HX284c0639f04e98580a9b12e081132e04',[
                "1" => $user->name,
                "2" => $appointment->date,
                "3" => $appointment->time,
                "4" => $doctor->name,
                "5" => $hospitalAddress,
                "6" => $doctor->user->phone_code . $doctor->user->phone,
            ]);
        }
    }
}
