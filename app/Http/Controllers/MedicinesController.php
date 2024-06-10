<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SuperAdmin\CustomController;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\Pharmacy;
use App\Models\Setting;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Gate;

class MedicinesController extends AppBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        return view('medicines.medicines');
    }

    public function getData(Request $request) {
        $perPage = $request->get('perPage', 10);
        $search = $request->get('search', '');
    
        $medicines = Medicine::where(function($query) use ($search) {
            if ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%')
                    ->orWhere('dci1', 'like', '%' . $search . '%')
                    ->orWhere('dosage1', 'like', '%' . $search . '%')
                    ->orWhere('unit_dosage1', 'like', '%' . $search . '%')
                    ->orWhere('shape', 'like', '%' . $search . '%')
                    ->orWhere('presentation', 'like', '%' . $search . '%');
            }
        })->paginate($perPage);
    
        return response()->json($medicines);
    }
    
    public function getData2(Request $request) {
        $perPage = $request->get('perPage', 10);
        $page = $request->get('page', 1);
        $sortBy = $request->get('sortBy', 'id');
        $sortDesc = $request->get('sortDesc', 'false') === 'true';
        $search = $request->get('search', '');

        $query = Medicine::query();

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('dosage1', 'LIKE', "%{$search}%")
                  ->orWhere('unit_dosage1', 'LIKE', "%{$search}%")
                  ->orWhere('shape', 'LIKE', "%{$search}%")
                  ->orWhere('presentation', 'LIKE', "%{$search}%")
                  ->orWhere('ppv', 'LIKE', "%{$search}%")
                  ->orWhere('price_br', 'LIKE', "%{$search}%")
                  ->orWhere('refund_rate', 'LIKE', "%{$search}%");
        }

        if ($sortBy) {
            $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
        }

        $medicines = $query->paginate($perPage, ['*'], 'page', $page);
        $total = $medicines->total();

        $data = [
            "medicines" => $medicines,
            "total" => $total
        ];

        return $this->sendDataResponse($data);
    }
}
