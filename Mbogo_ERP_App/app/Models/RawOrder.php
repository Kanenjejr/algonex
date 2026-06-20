<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawOrder extends Model
{
    use HasFactory;
    protected $table = 'raw_orders';
    protected $fillable = [
        'ord_date',
        'ord_no_bags',
        'ord_bag_size',
        'ord_tones',
        'recv_tones',
        'unrecv_tones',
        'raw_id',
        'customer_name',
        'order_price',
        'phone_no',
        'location',
        'user_id',
        'status',
        'company_id',
        'work_point_id',
    ];
    public function raw()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_id');
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
    public function recMaterials()
    {
        return $this->hasMany(RecMaterial::class, 'order_id');
    }
}
