<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;
    protected $table='commissions';
    protected $fillable = [
        'user_id','company_id','work_point_id','micro_transaction_id',
        'amount','description','status'
    ];

    public function user(){ return $this->belongsTo(User::class); }
    public function transaction(){ return $this->belongsTo(MicroTransaction::class,'micro_transaction_id'); }
    public function company(){ return $this->belongsTo(CompanySite::class,'company_id'); }
    public function workpoint(){ return $this->belongsTo(WorkPoint::class,'work_point_id'); }
}
