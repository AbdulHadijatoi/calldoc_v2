<?php

namespace App\Http\Controllers;

use App\Models\Prescription;

class PrescriptionRecordingController extends Controller
{

    /**
     * Show prescription recording to patient
     * 
     */
    public function index($prescription_id = null, $key_code = null)
    {
        if(!$prescription_id || !$key_code) {
            return abort('403',"Resource not accessible!");
        }

        $prescription = Prescription::where('id',$prescription_id)->where('key_code',$key_code)->first();

        if(!$prescription){
            return abort('404',"Prescription not found!");
        }

        
        return view('website.prescription_recording',compact('prescription'));
    }

    
}
