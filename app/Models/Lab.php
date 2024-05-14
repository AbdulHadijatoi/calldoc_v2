<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Lab extends Model
{
    use HasFactory;

    protected $table = 'lab';

    protected $fillable = ['name','user_id','address','lat','lng','status','image','start_time','end_time','commission'];

    protected $appends = ['fullImage'];

    protected function getFullImageAttribute()
    {
        return url('images/upload').'/'.$this->image;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function scopeGetByDistance($query, $lat, $lng, $radius)
    {
        if (isset($lat) && isset($lng) && $lat != null && $lng != null)
        {
            $results = DB::select(DB::raw('SELECT id, ( 3959 * acos( cos( radians(' . $lat . ') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(' . $lng . ') ) + sin( radians(' . $lat . ') ) * sin( radians(lat) ) ) ) AS distance FROM hospital HAVING distance < ' . $radius . ' ORDER BY distance'));
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
