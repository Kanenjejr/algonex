<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroTransaction extends Model
{
    use HasFactory;
    protected $table='micro_transactions';
    protected $fillable = [
        'user_id','company_id','work_point_id','bank_network_id',
        'tx_group','currency','amount','fx_rate','commission','meta','status'
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user(){ return $this->belongsTo(User::class); }
    public function company(){ return $this->belongsTo(CompanySite::class,'company_id'); }
    public function workpoint(){ return $this->belongsTo(WorkPoint::class,'work_point_id'); }

    public function bankNetwork(){ return $this->belongsTo(BankNetwork::class,'bank_network_id'); }

    public function commissions(){ return $this->hasMany(Commission::class,'micro_transaction_id'); }
}
