<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Hospital extends Model
{
    use HasFactory;

    protected $table = 'hospital';

    protected $fillable = ['name','phone','address','lat','lng','facility','status'];

    public function doctor()
    {
        return $this->hasMany('App\Models\Doctor');
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
