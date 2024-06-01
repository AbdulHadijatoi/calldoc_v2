<?php

namespace App\Http\Controllers\superAdmin;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\Setting;
use App\Models\Settle;
use App\Models\User;
use Carbon\Carbon;
use PDF;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Gate;
use OneSignal;
use App\Http\Controllers\SuperAdmin\CustomController;
use App\Mail\SendMail;
use App\Models\Country;
use App\Models\DoctorSubscription;
use App\Models\Hospital;
use App\Models\NotificationTemplate;
use App\Models\Notification;
use App\Models\UserAddress;
use App\Models\WorkingHour;
use App\Services\TwilioService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AppointmentController extends AppBaseController
{
    protected $twilioService;

    public function __construct() {
        $this->twilioService = new TwilioService();
    }
    
    public function sendTEST()
    {
        $this->twilioService->sendContentTemplate(
            "665474270",
            'HX64d3dea9dbf6ac1bb1095402ebe56d6d',
            [
                "1" => "Taha",
                "2" => "2024-04-19",
                "3" => "Doctor Taha",
                "4" => "Doctor Address",
                "5" => "2",
            ]
        );
        return "SENT NOTIFICATION";
    }

    public function calendar()
    {
        abort_if(Gate::denies('appointment_calendar_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return view('superAdmin.appointment.calendar');
    }

    public function inCalendar()
    {
        (new CustomController)->cancel_max_order();
        if(auth()->user()->hasRole('super admin'))
        {
            $appointments = Appointment::with('user')->get();
            return response(['success' => true , 'data' => $appointments]);
        }
        if(auth()->user()->hasRole('doctor'))
        {
            $appointments = Appointment::with('user')->get();
            return response(['success' => true , 'data' => $appointments]);
        }
    }

    public function commission()
    {
        (new CustomController)->cancel_max_order();
        abort_if(Gate::denies('commission_details'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $doctor = Doctor::where('user_id',auth()->user()->id)->first();
        $now = Carbon::today(env('timezone'));
        $appointments = array();
        for ($i = 0; $i < 7; $i++)
        {
            $appointment = Appointment::where('doctor_id',$doctor->id)->whereDate('created_at', $now)->get();
            $appointment['amount'] = $appointment->sum('amount');
            $appointment['admin_commission'] = $appointment->sum('admin_commission');
            $appointment['doctor_commission'] = $appointment->sum('doctor_commission');
            $now =  $now->subDay();
            $appointment['date'] = $now->toDateString();
            array_push($appointments,$appointment);
        }

        $currency = Setting::first()->currency_symbol;

        $past = Carbon::now(env('timezone'))->subDays(35);
        $now = Carbon::today(env('timezone'));
        $c = $now->diffInDays($past);
        $loop = $c / 10;
        $data = [];
        while ($now->greaterThan($past)) {
            $t = $past->copy();
            $t->addDay();
            $temp['start'] = $t->toDateString();
            $past->addDays(10);
            if ($past->greaterThan($now)) {
                $temp['end'] = $now->toDateString();
            } else {
                $temp['end'] = $past->toDateString();
            }
            array_push($data, $temp);
        }

        $settels = array();
        $orderIds = array();
        foreach ($data as $key)
        {
            $settle = Settle::where('doctor_id', $doctor->id)->where('created_at', '>=', $key['start'].' 00.00.00')->where('created_at', '<=', $key['end'].' 23.59.59')->get();
            $value['d_total_task'] = $settle->count();
            $value['admin_earning'] = $settle->sum('admin_amount');
            $value['doctor_earning'] = $settle->sum('doctor_amount');
            $value['d_total_amount'] = $value['admin_earning'] + $value['doctor_earning'];
            $remainingOnline = Settle::where([['doctor_id', $doctor->id], ['payment', 0],['doctor_status', 0]])->where('created_at', '>=', $key['start'].' 00.00.00')->where('created_at', '<=', $key['end'].' 23.59.59')->get();
            $remainingOffline = Settle::where([['doctor_id', $doctor->id], ['payment', 1],['doctor_status', 0]])->where('created_at', '>=', $key['start'].' 00.00.00')->where('created_at', '<=', $key['end'].' 23.59.59')->get();

            $online = $remainingOnline->sum('doctor_amount'); // admin e devana
            $offline = $remainingOffline->sum('admin_amount'); // admin e levana

            $value['duration'] = $key['start'] . ' - ' . $key['end'];
            $value['d_balance'] = $offline - $online; // + hoy to levana - devana
            array_push($settels,$value);
        }
        return view('superAdmin.appointment.commission',compact('doctor', 'appointments', 'currency','settels'));
    }

    public function show_settlement(Request $request)
    {
        $duration = explode(' - ',$request->duration);
        $currency = Setting::first()->currency_symbol;
        $settle = Settle::where('created_at', '>=', $duration[0].' 00.00.00')->where('created_at', '<=', $duration[1].' 23.59.59')->get();
        foreach($settle as $s)
        {
            $s->date = $s->created_at->toDateString();
        }
        return response(['success' => true , 'data' => $settle , 'currency' => $currency]);
    }

    public function acceptAppointment($appointment_id)
    {
        $appointment = Appointment::find($appointment_id);
        $appointment->update(['appointment_status' => 'approve']);
        // $this->notificationChange($appointment_id,'Accept');
        $user = $appointment->user;
        $doctor = $appointment->doctor;
        $hospital = $appointment->hospital;
        $hospitalAddress = $hospital?$hospital->address??null:null;
        if($hospitalAddress && $user){
            $lat = $hospital->lat;
            $long = $hospital->lng;
            $google_map_url = "https://www.google.com/maps?q=$hospitalAddress";
            Log::info('AppointmentController:acceptAppointment() patient notification',[
                $user->phone,'HX6bd65a12220813371257e5e0865c3c06',[
                    "1" => $user->name,
                    "2" => $appointment->date,
                    "3" => $appointment->time,
                    "4" => $doctor->name,
                    "5" => $hospitalAddress,
                    "6" => $doctor->user->phone_code . $doctor->user->phone,
                    "7" => $google_map_url,
                ]
            ]); // Log message
            $this->twilioService->sendContentTemplate($user->phone,'HX6bd65a12220813371257e5e0865c3c06',[
                "1" => $user->name,
                "2" => $appointment->date,
                "3" => $appointment->time,
                "4" => $doctor->name,
                "5" => $hospitalAddress,
                "6" => $doctor->user->phone_code . $doctor->user->phone,
                "7" => $google_map_url,
            ]);
        }

        // ONLY WHEN ($appointment->is_from == 0) THEN SEND NOTIFICATION TO DOCTOR
        if($appointment->is_from == 0){
            Log::info('AppointmentController:acceptAppointment() doctor notification',[
                $doctor->user->phone,"HXb58ab4662e8c9824ff9dd50fa84b1dd7", [
                    "1" => $doctor->name,
                    "2" => $appointment->id,
                    "3" => $appointment->date,
                    "4" => $appointment->time,
                    "5" => $user->name,
                    "6" => $user->phone,
                ]
            ]); // Log message
            $this->twilioService->sendContentTemplate($doctor->user->phone,"HXb58ab4662e8c9824ff9dd50fa84b1dd7", [
                "1" => $doctor->name,
                "2" => $appointment->id,
                "3" => $appointment->date,
                "4" => $appointment->time,
                "5" => $user->name,
                "6" => $user->phone,
            ]);
        }

        return redirect()->back()->with('status',__('status change successfully...!!'));
    }

    public function cancelAppointment($appointment_id)
    {
        $appointment = Appointment::find($appointment_id);
        $appointment->update(['appointment_status' => 'cancel']);

        // Cancel Appointment to patient from DOCTOR
        $doctor = $appointment->doctor;
        $hospital = $appointment->hospital;
        $user = $appointment->user;
        if($hospital->address && $user){
            Log::info('AppointmentController:cancelAppointment() cancel doctor notification',[
                $user->phone,'HX0ba3274473ee4eb9ca629b66ad636039',[
                    "1" => $user->name,
                    "2" => $appointment->date,
                    "3" => $appointment->time,
                    "4" => $appointment->doctor->name,
                    "5" => $hospital->address??"-",
                    "6" => $doctor->user->phone_code . $doctor->user->phone,
                ]
            ]); // Log message
            $this->twilioService->sendContentTemplate($user->phone,'HX0ba3274473ee4eb9ca629b66ad636039',[
                "1" => $user->name,
                "2" => $appointment->date,
                "3" => $appointment->time,
                "4" => $appointment->doctor->name,
                "5" => $hospital->address??"-",
                "6" => $doctor->user->phone_code . $doctor->user->phone,
            ]);
        }
        return redirect()->back()->with('status',__('status change successfully...!!'));
    }

    public function completeAppointment($appointment_id)
    {
        $appointment = Appointment::find($appointment_id);
        Appointment::find($appointment_id)->update(['appointment_status' => 'complete','payment_status' => 1]);
        $doctor = Doctor::where('user_id',auth()->user()->id)->first();
        if($doctor->based_on == 'commission')
        {
            $settle = array();
            $settle['appointment_id'] = $appointment->id;
            $settle['doctor_id'] = $appointment->doctor_id;
            $settle['doctor_amount'] = $appointment->doctor_commission;
            $settle['admin_amount'] = $appointment->admin_commission;
            $settle['payment'] = $appointment->payment_type == 'COD' ? 0 : 1;
            $settle['doctor_status'] = 0;
            Settle::create($settle);
        }
        // $this->notificationChange($appointment_id,'Complete');
        
        return redirect()->back()->with('status',__('status change successfully...!!'));
    }

    // change Appointment to user
    public function notificationChange($appointment_id,$status)
    {
        $setting = Setting::first();
        $appointment = Appointment::with('user')->find($appointment_id);
        $notification_template = NotificationTemplate::where('title','status change')->first();
        $msg_content = $notification_template->msg_content;
        $mail_content = $notification_template->mail_content;
        $detail['user_name'] = $appointment->user->name;
        $detail['appointment_id'] = $appointment->appointment_id;
        $detail['date'] = $status;
        $detail['status'] = $appointment->date;
        $detail['app_name'] = $setting->business_name;
        $user_data = ["{{user_name}}","{{appointment_id}}","{{date}}","{{status}}","{{app_name}}"];
        $mail1 = str_replace($user_data, $detail, $mail_content);
        $message1 = str_replace($user_data, $detail, $msg_content);
        if($setting->patient_mail == 1){
            try {
                $config = array(
                    'driver'     => $setting->mail_mailer,
                    'host'       => $setting->mail_host,
                    'port'       => $setting->mail_port,
                    'from'       => array('address' => $setting->mail_from_address, 'name' => $setting->mail_from_name),
                    'encryption' => $setting->mail_encryption,
                    'username'   => $setting->mail_username,
                    'password'   => $setting->mail_password
                );
                Config::set('mail', $config);
                Mail::to(auth()->user()->email)->send(new SendMail($mail1,$notification_template->subject));
            } catch (\Throwable $th) {

            }
        }

        if($setting->patient_notification == 1){
            try {
                Config::set('onesignal.app_id', $setting->patient_app_id);
                Config::set('onesignal.rest_api_key', $setting->patient_api_key);
                Config::set('onesignal.user_auth_key', $setting->patient_auth_key);
                OneSignal::sendNotificationToUser(
                    $message1,
                    $appointment->user->device_token,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null,
                    $setting->business_name
                );
            } catch (\Throwable $th) {
            }
        }

        $user_notification = array();
        $user_notification['user_id'] = auth()->user()->id;
        $user_notification['doctor_id'] = $appointment->doctor_id;
        $user_notification['user_type'] = 'user';
        $user_notification['title'] = 'create appointment';
        $user_notification['message'] = $message1;
        Notification::create($user_notification);
        return true;
    }

    public function appointment()
    {
        (new CustomController)->cancel_max_order();
        abort_if(Gate::denies('appointment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $currency = Setting::first()->currency_symbol;
        if(auth()->user()->hasRole('super admin'))
        {
            $appointments = Appointment::with(['doctor','address'])->orderBy('id','DESC')->get();
        }
        else
        {
            $doctor = Doctor::where('user_id',auth()->user()->id)->first();
            $appointments = Appointment::with(['doctor','address','hospital'])->where('doctor_id',$doctor->id)->orderBy('id','DESC')->get();
            foreach ($appointments as $appointment)
            {
                if(Prescription::where('appointment_id',$appointment->id)->first())
                {
                    $appointment->prescription = '1';
                    $appointment->preData = Prescription::where('appointment_id',$appointment->id)->first();
                }
                else
                {
                    $appointment->prescription = '0';
                }
            }
        }
        return view('superAdmin.appointment.appointment',compact('appointments','currency'));
    }
    
    public function getAppointments($type = null)
    {
        (new CustomController)->cancel_max_order();
        abort_if(Gate::denies('appointment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('superAdmin.appointment.appointment', compact('type'));
    }
    
    public function getAppointmentsData(Request $request) {
        $type = null;
        if($request->type){
            $type = $request->type;
        }

        $currency = Setting::first()->currency_symbol;

        $doctor = Doctor::where('user_id', auth()->user()->id)->first();
        
        $query = Appointment::with(['doctor', 'address', 'hospital'])->where('doctor_id', $doctor->id);

        if ($type === 'today') {
            $query->whereDate('date', now()->toDateString());
        } elseif ($type === 'tomorrow') {
            $query->whereDate('date', now()->addDay()->toDateString());
        }

        $appointments = $query->orderBy('id', 'DESC')->paginate($request->per_page);
        $total = $appointments->total();

        foreach ($appointments as $appointment) {
            $prescription = $appointment->getPrescription;
            if ($prescription) {
                $appointment->prescription = '1';// this is not the way but lets proceed anyways
                $appointment->preData = $prescription;
            } else {
                $appointment->prescription = '0';
            }
            // $appointment->patient_name = $appointment->user ? $appointment->user->name ?? '-' : '-';
        }

        $data = [
            "currency" => $currency,
            "appointments" => $appointments,
            "total_appointments" => $total,
            "is_doctor" => auth()->user()->hasRole('doctor') ? 1 : 0,
        ];

        return $this->sendDataResponse($data);
    }


    public function show_appointment($appointment_id)
    {
        (new CustomController)->cancel_max_order();
        $currency = Setting::first()->currency_symbol;
        $appointment = Appointment::with(['doctor','address','hospital'])->find($appointment_id);
        return response(['success' => true , 'data' => $appointment , 'currency' => $currency]);
    }

    public function prescription($appointment_id)
    {
        (new CustomController)->cancel_max_order();
        $appointment = Appointment::with(['doctor','user'])->find($appointment_id);
        $doctor = Doctor::with(['expertise','treatment','category'])->find($appointment->doctor_id);
        $medicines = Medicine::whereStatus('1')->get();
        return view('superAdmin.doctor.prescription',compact('appointment','doctor','medicines'));
    }

    public function all_medicine()
    {
        $medicines = Medicine::whereStatus('1')->get();
        return response(['success' => true , 'data' => $medicines]);
    }

    public function addPrescription_old(Request $request)
    {
        $data = $request->all();
        $medicine = array();
        for ($i = 0; $i < count($data['medicine']); $i++)
        {
            $temp['medicine'] = isset($data['medicine'][$i])?$data['medicine'][$i]:'';
            $temp['day'] = isset($data['day'][$i])?$data['day'][$i]:'0';
            $temp['qty_morning'] = isset($data['qty_morning'][$i])?$data['qty_morning'][$i]:'0';
            $temp['qty_afternoon'] = isset($data['qty_afternoon'][$i])?$data['qty_afternoon'][$i]:'0';
            $temp['qty_night'] = isset($data['qty_night'][$i])?$data['qty_night'][$i]:'0';
            $temp['remarks'] = isset($data['remarks'][$i])?$data['remarks'][$i]:'';
            array_push($medicine,$temp);
        }
        $pre['medicines'] = json_encode($medicine);
        $pre['appointment_id'] = $data['appointment_id'];
        $user = auth()->user();
        if(!$user->doctor){
            return back()->with('status',__('Doctor not found!'));
        }
        $pre['doctor_id'] = $user->doctor? $user->doctor->id: null;

        $pre['user_id'] = $data['user_id'];
        $pres = Prescription::create($pre);
        $prescription = Prescription::with(['doctor','user'])->find($pres->id);
        $prescription->doctorUser = User::find($prescription->doctor['user_id']);

        $medicineName = $pres->medicines;
        $pdf = PDF::loadView('temp', compact('medicineName'));
        $path = public_path() . '/prescription/upload';
        $fileName =  uniqid() . '.' . 'pdf' ;
        $pdf->save($path . '/' . $fileName);
        $pres->pdf = $fileName;
        $pres->save();
        return redirect('/appointment');
    }

    public function saveRecording(Request $request) {
        try {
            $request->validate([
                'recording' => 'required|file|mimes:webm',
                'appointment_id' => 'required|exists:appointment,id',
            ]);

            $appointment = Appointment::find($request->appointment_id);

            $recording = $request->file('recording');
            $file_name = 'recording_' . time() . '.' . $recording->getClientOriginalExtension();
            $recording->move(public_path('prescription/recordings'), $file_name);
            
            $randomString = Str::random(50);
            $pres = Prescription::find($request->appointment_id);
            if($pres){
                $pres->recording = "prescription/recordings/". $file_name;
                $pres->key_code = $randomString;
            }else{
                $pres = new Prescription();
                $pres->appointment_id = $request->appointment_id;
                $pres->medicines = '';
                $pres->user_id = $appointment->user_id;
                $pres->doctor_id = auth()->user()->doctor ? auth()->user()->doctor->id : null;
                $pres->recording = "prescription/recordings/". $file_name;
                $pres->key_code = $randomString;
            }
            $pres->save();

            $user = $appointment->user;
            $doctor = $appointment->doctor;
            $hospital = $appointment->hospital;
            $hospitalAddress = $hospital?$hospital->address??null:null;
            if($hospitalAddress && $user){
                Log::info('AppointmentController:cancelAppointment() cancel doctor notification',[
                    $user->phone,'HX4d21db0c85b7cc4dbe7653c610a33f26',[
                    "1" => $user->name,
                    "2" => $appointment->date,
                    "3" => $doctor->name,
                    "4" => $hospitalAddress,
                    "5" => url("prescription_recording/".$pres->id."/".$pres->key_code)
                    ]
                ]); // Log message
                $this->twilioService->sendContentTemplate($user->phone,'HX4d21db0c85b7cc4dbe7653c610a33f26',[
                    "1" => $user->name,
                    "2" => $appointment->date,
                    "3" => $doctor->name,
                    "4" => $hospitalAddress,
                    "5" => url("prescription_recording/".$pres->id."/".$pres->key_code),
                ]);
            }

            return response()->json([
                'message' => 'Audio clip saved successfully',
                'pres_id'=>$pres->id
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
    
    public function addPrescription(Request $request){
        $request->validate([
            'prescription_image' => 'nullable|image|max:4096',
            'appointment_id' => 'required|exists:appointment,id',
        ]);
        
        $prescription_image = null;
        if($request->hasFile('prescription_image')) {
            $prescription_image = (new CustomController)->imageUpload($request->prescription_image);
        }

        $data = $request->all();

        // Handle medicine data
        $medicine = [];
        if(isset($data['medicine']) && count($data['medicine']) > 0){
            for ($i = 0; $i < count($data['medicine']); $i++) {
                $temp['medicine'] = isset($data['medicine'][$i]) ? $data['medicine'][$i] : '';
                $temp['day'] = isset($data['day'][$i]) ? $data['day'][$i] : '0';
                $temp['qty_morning'] = isset($data['qty_morning'][$i]) ? $data['qty_morning'][$i] : '0';
                $temp['qty_afternoon'] = isset($data['qty_afternoon'][$i]) ? $data['qty_afternoon'][$i] : '0';
                $temp['qty_night'] = isset($data['qty_night'][$i]) ? $data['qty_night'][$i] : '0';
                $temp['remarks'] = isset($data['remarks'][$i]) ? $data['remarks'][$i] : '';
                array_push($medicine, $temp);
            }
        }

        $pres_id = $request->pres_id;
        // Create prescription data
        $pres = null;
        if($pres_id){
            $pres = Prescription::find($pres_id);
        }

        if(!$pres){
            $pres = new Prescription();
            $pres->appointment_id = $data['appointment_id'];
            $pres->user_id = $data['user_id'];
            $pres->doctor_id = auth()->user()->doctor ? auth()->user()->doctor->id : null;
        }
    
        $pres->medicines = json_encode($medicine);
        if($prescription_image){
            $pres->prescription_image = $prescription_image;
        }
    
        $pres->save();

        // Generate and save PDF
        $pdf = PDF::loadView('temp', ["medicine"=>$pres->medicines]);
        $pdfFileName = uniqid() . '.pdf';
        $pdf->save(public_path('prescription/upload/' . $pdfFileName));
        $pres->pdf = $pdfFileName;
        $pres->save();

        if($prescription_image){
            $appointment = Appointment::find($pres->appointment_id);
            
            $user = $appointment->user;
            $doctor = $appointment->doctor;
            $hospital = $appointment->hospital;
            $hospitalAddress = $hospital?$hospital->address??null:null;

            $doctor_phone = $doctor->user->phone_code . $doctor->user->phone;
            if (!preg_match('/^\+?212/', $doctor_phone)) {
                $doctor_phone = '+212' . ltrim($doctor_phone, '0');
            }
            $this->twilioService->sendContentTemplate($user->phone,"HX0be4101d36af0ea6ecf90964012b954d", [
                "1" => $user->name,
                "2" => $hospitalAddress,
                "3" => $appointment->date,
                "4" => $doctor->name,
                "5" => $doctor_phone,
                "6" => "images/upload/$prescription_image",
            ]);
        }
        return redirect('/appointments');
    }



    public function changeTimeslot(Request $request)
    {
        $doctor = Doctor::where('user_id',auth()->user()->id)->first();
        $timeslots = (new CustomController)->timeSlot($doctor->id,$request->date);
        return response(['success' => true , 'data' => $timeslots]);
    }

    public function createAppointment($id)
    {
        $patient = User::where('id',$id)->first();
        $doctor = Doctor::with(['category','expertise'])->where('user_id',auth()->user()->id)->first();
        $hosp = explode(',',$doctor->hospital_id);
        $hospitals = Hospital::whereIn('id',$hosp)->get();
        $patient_addressess = UserAddress::where('user_id',$id)->get();
        $date = Carbon::now(env('timezone'))->format('Y-m-d');
        $timeslots = (new CustomController)->timeSlot($doctor->id,$date);
        $setting = Setting::first();
        return view('superAdmin.appointment.create_appointment',compact('setting','patient','doctor','hospitals','date','patient_addressess','timeslots'));
    }

    public function storeAppointment(Request $request,$id)
    {
        $data = $request->all();
        $request->validate([
            'illness_information' => 'nullable',
            'age' => 'nullable|numeric',
            'patient_address' => 'bail|required',
            'drug_effect' => 'nullable',
            'note' => 'nullable',
            'date' => 'bail|required',
            'time' => 'bail|required',
            'hospital_id' => 'bail|required',
        ]);
        $patient = User::where('id',$id)->first();
        $doctor = Doctor::where('user_id',auth()->user()->id)->first();
        $data['appointment_id'] =  '#' . rand(100000, 999999);
        $data['appointment_status'] = 'pending';
        $data['patient_name'] = $patient->name;
        $data['phone_no'] = $patient->phone;
        $data['user_id'] = $id;
        $data['doctor_id'] = $doctor->id;
        $data['appointment_for'] = 'my_self';
        $data['payment_status'] = 0;
        $data['is_from'] = 1;
        if($request->hasFile('report_image'))
        {
            $report = [];
            for ($i=0; $i < count($data['report_image']); $i++)
            {
                // return $request->report_image[$i];
                 array_push($report,(new CustomController)->imageUpload($request->report_image[$i]));
            }
            $data['report_image'] = json_encode($report);
        }
        // dd($data);

        if($doctor->based_on == 'commission') {
            // Ensure neither appointment_fees nor commission_amount is zero
            if ($doctor->appointment_fees != 0 && $doctor->commission_amount != 0) {
                // Calculate the commission if both values are non-zero
                $comm = $doctor->appointment_fees * $doctor->commission_amount;
                // Calculate admin and doctor commissions
                $data['admin_commission'] = intval($comm / 100);
                $data['doctor_commission'] = intval($doctor->appointment_fees - $data['admin_commission']);
            } else {
                // Handle the case when either value is zero
                // For example, you could set admin_commission and doctor_commission to zero
                $data['admin_commission'] = 0;
                $data['doctor_commission'] = $doctor->appointment_fees; // No commission, so all goes to the doctor
            }
        }
        
        else
        {
            DoctorSubscription::where('doctor_id',$doctor->id)->latest()->first()->increment('booked_appointment');
        }
        $data['amount'] = $doctor->appointment_fees;
        $data['payment_type'] = 'COD';
        $data = array_filter($data, function($a) {return $a !== "";});
        Appointment::create($data);
        return redirect('appointment')->with('status',__('Appointment Add successfully...!!'));
    }
    public function editAppointment($id)
    {
        $appointment = Appointment::where('id',$id)->first();
        $patient = User::where('id',$appointment->user_id)->first();
        $doctor = Doctor::where('id',$appointment->doctor_id)->first();
        $hosp = explode(',',$doctor->hospital_id);
        $hospitals = Hospital::whereIn('id',$hosp)->get();
        $patient_addressess = UserAddress::where('user_id',$appointment->user_id)->get();
        $date = $appointment->date;
        $timeslots = (new CustomController)->timeSlot($doctor->id,$date);
        $setting = Setting::first();
        return view('superAdmin.appointment.edit_appointment',compact('setting','appointment','hospitals','patient_addressess','date','timeslots','patient'));
    }

    public function updateAppointment(Request $request,$id)
    {
        $data = $request->all();
        $request->validate([
            'illness_information' => 'nullable',
            'age' => 'nullable|numeric',
            'patient_address' => 'bail|required',
            'drug_effect' => 'nullable',
            'note' => 'nullable',
            'date' => 'bail|required',
            'time' => 'bail|required',
            'hospital_id' => 'bail|required',
        ]);

        $appointment = Appointment::find($id);
        if($appointment->date != $request->date)
        {
            $request->validate([
                'date' => 'bail|required|after:yesterday',
            ],
            [
                'date.after' => 'Date must be future date.',
            ]
        );
        }
        $patient = User::where('id',$appointment->user_id)->first();
        $doctor = Doctor::where('id',$appointment->doctor_id)->first();
        $data['appointment_id'] =  $appointment->appointment_id;
        $data['appointment_status'] = $appointment->appointment_status;
        $data['patient_name'] = $appointment->patient_name;
        $data['phone_no'] = $appointment->phone_no;
        $data['user_id'] = $patient->id;
        $data['doctor_id'] = $doctor->id;
        $data['appointment_for'] = $appointment->appointment_for;
        $data['payment_status'] = $appointment->payment_status;
        $data['is_from'] = 1;
        $report = [];
        for ($i=0; $i < 3; $i++)
        {
            $report_img = json_decode(DB::table('appointment')->where('id',$appointment->id)->value('report_image'));
            if ($data['type'.$i] === 'new')
            {
                if($i == $data['change_iteration'.$i] && $data['change_iteration'.$i] != null)
                {
                    if(isset($appointment->report_image[$i]))
                        (new CustomController)->deleteFile($report_img[$i]);
                    array_push($report,(new CustomController)->imageUpload($request->file('report_image')[$i]));
                }
            }
            if ($data['type'.$i] === 'old') {
                array_push($report,$report_img[$i]);

            }
        }
        if (count($report)>0)
            $data['report_image'] = json_encode($report);

        $data['amount'] = $doctor->appointment_fees;
        $data['payment_type'] = $appointment->payment_type;
        $data = array_filter($data, function($a) {return $a !== "";});
        $appointment->update($data);

        // UPDATE APPOINTMENT FROM DOCTOR TO PATIENT
        $user = $appointment->user;
        $doctor = $appointment->doctor;
        $hospital = $appointment->hospital;
        $hospitalAddress = $hospital?$hospital->address??null:null;
        if($hospitalAddress && $user){
            $lat = $hospital->lat;
            $long = $hospital->lng;
            $google_map_url = "https://www.google.com/maps?q=$hospitalAddress";

            Log::info('AppointmentController:updateAppointment() update user notification',[
                $user->phone,'HX9d3acc90ddfe9185394ac540873faac4',[
                    "1" => $user->name,
                    "2" => $appointment->date,
                    "3" => $appointment->time,
                    "4" => $doctor->name,
                    "5" => $hospitalAddress,
                    "6" => $doctor->user->phone_code . $doctor->user->phone,
                    "7" => $google_map_url,
                ]
            ]); // Log message

            $this->twilioService->sendContentTemplate($user->phone,'HX9d3acc90ddfe9185394ac540873faac4',[
                "1" => $user->name,
                "2" => $appointment->date,
                "3" => $appointment->time,
                "4" => $doctor->name,
                "5" => $hospitalAddress,
                "6" => $doctor->user->phone_code . $doctor->user->phone,
                "7" => $google_map_url,
            ]);
        }

        return redirect('appointment')->with('status',__('Appointment Update successfully...!!'));
    }

    public function deleteAppointment($id)
    {
        $appointment  = Appointment::find($id);

        if(isset($appointment->image))
        {
            for ($i=0; $i < count($appointment['report_image']); $i++)
            {
                (new CustomController)->deleteFile($appointment->report_image[$i]);
            }
        }
        $appointment->delete();
        return redirect()->back()->with('status',__('Appointment Delete successfully...!!'));
    }

    public function addAddr(Request $request)
    {
        $request->validate([
            'address' => 'bail|required',
            'lang' => 'bail|required',
            'lat' => 'bail|required',
        ]);
        $userAddress = UserAddress::create($request->all());
        return response(['success' => true,'data' => $userAddress]);
    }
}
