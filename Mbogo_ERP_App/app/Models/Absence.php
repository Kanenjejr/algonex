<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id','company_id','work_point_id','date','days','deduction_amount','reason','status','approved_by',
        'calendar_days',
        'paid_days',
        'daily_rate',
        'deduction_is_auto',
    ];
    protected $casts = [
        'date' => 'date',
        'days' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'calendar_days' => 'integer',
        'paid_days' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'deduction_is_auto' => 'boolean',
    ];
    public function user() { return $this->belongsTo(User::class); }
    public function approver() { return $this->belongsTo(User::class,'approved_by'); }
    public function company() { return $this->belongsTo(CompanySite::class,'company_id'); }
    public function workpoint() { return $this->belongsTo(WorkPoint::class,'work_point_id'); }
}