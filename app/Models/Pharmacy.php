<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Pharmacy extends Model
{
    use HasFactory;

    protected $table = 'pharmacy';

    protected $fillable = ['user_id','description','image','name','email','phone','address','lat','lang','start_time','end_time','commission_amount','status','is_shipping','delivery_charges','language'];

    protected $appends = ['fullImage'];

    protected function getFullImageAttribute()
    {
        return url('images/upload').'/'.$this->image;
    }

    public function scopeGetByDistance($query, $lat, $lng, $radius)
    {
        if ($lat != null && $lng != null) {
            $results = DB::select(DB::raw('SELECT id, ( 3959 * acos( cos( radians(' . $lat . ') ) * cos( radians( lat ) ) * cos( radians( lang ) - radians(' . $lng . ') ) + sin( radians(' . $lat . ') ) * sin( radians(lat) ) ) ) AS distance FROM pharmacy HAVING distance < ' . $radius . ' ORDER BY distance'));
            if (!empty($results))
            {
                $ids = [];
                //Extract the id's
                foreach ($results as $q)
                {
                    array_push($ids, $q->id);
                }
                return $query->whereIn('id', $ids);
            }
            return $query->whereIn('id', []);
        }
        else
            return $query->whereIn('id', []);
    }
}
