<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeslbLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','company_id','work_point_id','original_amount','outstanding_balance',
        'monthly_rate','start_date','end_date','status','notes'
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'monthly_rate' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function company() { return $this->belongsTo(CompanySite::class, 'company_id'); }
    public function workpoint() { return $this->belongsTo(WorkPoint::class, 'work_point_id'); }
    public function payments() { return $this->hasMany(HeslbLoanPayment::class); }
}
