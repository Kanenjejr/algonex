<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrdReqstHeader extends Model
{
    use HasFactory;
    protected $table = 'prd_reqst_headers';
    protected $fillable = [
        'company_id',
        'work_point_id',
        'User_id',
        'RequestNo',
        'RequestDate',
        'total_amount',
        'Status',
        'approved_by',
        'approved_at',
        'remarks',
    ];
    protected $dates = ['RequestDate','approved_at'];
    public function items()
    {
        return $this->hasMany(PrdReqstItem::class, 'prd_reqst_header_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'User_id');
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