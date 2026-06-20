<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CstmOrder extends Model
{
    use HasFactory;
    protected $table = 'cstm_orders';
    protected $fillable = [
        'cstm_id','order_no','order_date','total_amount','currency','type','status',
        'user_id','company_id','work_point_id'
    ];
    protected $dates = ['order_date'];
    public function customer() { return $this->belongsTo(CstmSply::class, 'cstm_id'); }
    public function items() { return $this->hasMany(CstmProduct::class, 'order_id'); }
    public function user() { return $this->belongsTo(User::class, 'user_id'); }
}