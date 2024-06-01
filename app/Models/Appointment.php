<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'appointment';

    protected $fillable = ['user_id','appointment_id','hospital_id','doctor_id','cancel_by','cancel_reason','payment_status','amount','payment_type','appointment_for','patient_name','age','report_image','drug_effect','patient_address','phone_no','date','time','payment_token','appointment_status','illness_information','note','doctor_commission','admin_commission','discount_id','discount_price','is_from','is_insured','policy_insurer_name','policy_number','notification_sent'];

    public function getPrescription()
    {
        return $this->hasOne(Prescription::class, 'appointment_id');
    }
    
    public function getPrescriptions()
    {
        return $this->hasMany(Prescription::class, 'appointment_id');
    }

    public function doctor()
    {
        return $this->belongsTo('App\Models\Doctor');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function hospital()
    {
        return $this->belongsTo('App\Models\Hospital');
    }

    public function address()
    {
        return $this->belongsTo('App\Models\UserAddress','patient_address','id');
    }

    protected $appends = ['rate','review'];

    public function getreportImageAttribute()
    {
        if(isset($this->attributes['report_image']) && $this->attributes['report_image'] != null)
        {
            $images = array();
            $image = json_decode($this->attributes['report_image']);
            if($image){

                for ($i=0; $i < count($image); $i++)
                {
                    array_push($images,url('images/upload').'/'.$image[$i]);
                }
            }

            return $images;
        }
        else
        {
            return [];
        }

    }

    public function getRateAttribute()
    {
        $review = Review::where('appointment_id',$this->attributes['id'])->get();
        if (count($review) > 0) {
            $totalRate = 0;
            foreach ($review as $r)
            {
                $totalRate = $totalRate + $r->rate;
            }
            return round($totalRate / count($review), 1);
        }
        else
        {
            return 0;
        }
    }

    public function getReviewAttribute()
    {
        return Review::where('appointment_id',$this->attributes['id'])->count();
    }

    
    public function scopeAppointmentsIn24Hours($query, $toleranceMinutes = 10)
    {

        $now = Carbon::now();

        $twentyFourHoursFromNow = $now->copy()->addHours(24);

        $startTime = $twentyFourHoursFromNow->copy()->subMinutes($toleranceMinutes);
        $endTime = $twentyFourHoursFromNow->copy();

        return $query->where('notification_24_hours_sent', 0)
                    ->where('date', $twentyFourHoursFromNow->toDateString())
                    ->whereBetween('time', [$startTime->format("h:i:s"), $endTime->format("h:i:s")]);
    }

   

    public function scopeAppointmentsWithin10Days($query) {
        $tenDaysFromNow = Carbon::now()->addDays(10);

        return $query->where("notification_10_days_sent", '==', 0)
                    ->where('date', $tenDaysFromNow->toDateString());
    }
}
