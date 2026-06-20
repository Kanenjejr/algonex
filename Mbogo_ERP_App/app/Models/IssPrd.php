<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssPrd extends Model
{
    use HasFactory;
    protected $table = 'iss_prds';

    protected $fillable = [
        'order_id',
        'prd_id',
        'issue_date',
        'issue_qnty',
        'iss_unit',
        'user_id',
        'received_by',
        'status',
        'company_id',
        'work_point_id',
    ];
    public function order()
    {
        return $this->belongsTo(PrdOrder::class, 'order_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'prd_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }
    public function workpoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }
}
