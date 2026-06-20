<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssMaterial extends Model
{
    use HasFactory;
    protected $table = 'iss_materials';

    protected $fillable = [
        'iss_date',
        'raw_id',
        'iss_no_bags',
        'iss_bag_size',
        'iss_tones',
        'company_id',
        'work_point_id',
        'user_id',
        'status',
    ];
    public function raw()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_id');
    }
    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }
    public function workpoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}