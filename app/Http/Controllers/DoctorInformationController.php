<?php

namespace App\Http\Controllers;

use App\Models\DoctorInformation;
use Illuminate\Http\Request;

class DoctorInformationController extends Controller
{

    public function index()
    {
        return view('doctors-information.index');
    }


    public function getData(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $search = $request->get('search', '');

        $doctors = DoctorInformation::where(function($query) use ($search) {
            if ($search) {
                $query->where('business_name', 'like', '%' . $search . '%')
                    ->orWhere('business_phone', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhere('sub_types', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere('full_address', 'like', '%' . $search . '%')
                    ->orWhere('street', 'like', '%' . $search . '%')
                    ->orWhere('city', 'like', '%' . $search . '%')
                    ->orWhere('country', 'like', '%' . $search . '%');
            }
        })->paginate($perPage);

        return response()->json($doctors);
    }
}
