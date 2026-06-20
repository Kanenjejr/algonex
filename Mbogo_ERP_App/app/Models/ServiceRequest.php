<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;
    protected $table = 'service_requests';
    protected $fillable = [
        'company_id',
        'work_point_id',
        'User_id',
        'RequestNo',
        'RequestDate',
        'ServiceType',
        'Description',
        'estimated_cost',
        'Status',
        'approved_by',
        'approved_at',
        'remarks',
    ];
    protected $dates = ['RequestDate','approved_at'];
    public function user()
    {
        return $this->belongsTo(User::class,'User_id');
    }
    public function workpoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }
    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }
}