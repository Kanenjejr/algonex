<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id','work_point_id','period','prepared_at','prepared_by',
        'approved_at','approved_by','paid_at','paid_by','status',
        'gross_total','net_total',
        'nssf_employee_rate','nssf_employer_rate','psssf_rate','sdl_rate','wcf_rate','notes',
        'scope_type',
        'include_ncl',
        'days_in_month',
        'allowance_total',
        'bonus_total',
        'absence_total',
        'heslb_total',
        'loan_total',
        'paye_total',
        'employer_cost_total',
        'payroll_cost_total',
        'rolled_back_at',
        'rolled_back_by',
        'rollback_reason',
    ];
    public function lines() { return $this->hasMany(PayrollLine::class); }
    public function company() { return $this->belongsTo(CompanySite::class,'company_id'); }
    public function workpoint() { return $this->belongsTo(WorkPoint::class,'work_point_id'); }
    public function preparer() { return $this->belongsTo(User::class,'prepared_by'); }
    public function approver() { return $this->belongsTo(User::class,'approved_by'); }
    public function payer() { return $this->belongsTo(User::class,'paid_by'); }
}