<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CstmSply extends Model
{
    use HasFactory;
    protected $table = 'cstm_splies';

    protected $fillable = [
        'customer_name','phone_no','location',
        'address_line','city','state','postal_code','country',
        'category','status','user_id','company_id','work_point_id'
    ];

    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    public function company() { return $this->belongsTo(CompanySite::class, 'company_id'); }
    public function workpoint() { return $this->belongsTo(WorkPoint::class, 'work_point_id'); }

    public function orders() { return $this->hasMany(CstmOrder::class, 'cstm_id'); }
    public function transactions() { return $this->hasMany(CstmTx::class, 'cstm_id'); }
    public function items() { return $this->hasMany(CstmProduct::class, 'cstm_id'); }
}