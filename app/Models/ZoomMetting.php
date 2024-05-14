<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoomMetting extends Model
{
    use HasFactory;
    protected $table = 'zoom_meeting';
    protected $fillable = ['doctor_id','zoom_api_url','zoom_api_key','zoom_api_secret'];

}
