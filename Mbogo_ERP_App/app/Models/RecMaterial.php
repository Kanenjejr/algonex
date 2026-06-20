<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecMaterial extends Model
{
    use HasFactory;
    protected $table = 'rec_materials';
    protected $fillable = [
        'rcv_date',
        'raw_id',
        'rcv_no_bags',
        'rcv_bag_size',
        'rcv_tones',
        'order_id',
        'company_id',
        'work_point_id',
        'user_id',
        'status',
    ];
    public function raw()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_id');
    }
    public function order()
    {
        return $this->belongsTo(RawOrder::class, 'order_id');
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
