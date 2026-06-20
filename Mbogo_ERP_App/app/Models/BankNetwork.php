<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankNetwork extends Model
{
    use HasFactory;
    protected $table='bank_networks';
    protected $fillable = [
        'user_id','company_id','work_point_id',
        'type','name','code','account_or_wallet','branch','status'
    ];

    // relations
    public function user(){ return $this->belongsTo(User::class); }
    public function company(){ return $this->belongsTo(CompanySite::class,'company_id'); }
    public function workpoint(){ return $this->belongsTo(WorkPoint::class,'work_point_id'); }

    public function transactions(){ return $this->hasMany(MicroTransaction::class,'bank_network_id'); }
}
