<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorInformation extends Model
{
    use HasFactory;

    protected $table = 'doctors_information';
    
    protected $fillable = [
        'query',
        'google_place_url',
        'business_name',
        'business_phone',
        'type',
        'sub_types',
        'category',
        'full_address',
        'street',
        'city',
        'country',
        'latitude',
        'longitude',
    ];
}
