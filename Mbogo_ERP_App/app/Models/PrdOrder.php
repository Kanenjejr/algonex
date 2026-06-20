<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrdOrder extends Model
{
    use HasFactory;
    protected $table = 'prd_orders';

    protected $fillable = [
        'ord_date',
        'ord_qnty',
        'iss_qnty',
        'uniss_qnty',
        'ord_unit',
        'prd_id',
        'customer_name',
        'phone_no',
        'location',
        'user_id',
        'status',
        'company_id',
        'work_point_id',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'prd_id');
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
