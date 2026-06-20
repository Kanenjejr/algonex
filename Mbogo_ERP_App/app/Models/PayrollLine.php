<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollLine extends Model
{
    use HasFactory;
    protected $fillable = [
        'payroll_id',
        'user_id',
        'company_id',
        'work_point_id',
        'basic_salary',
        'allowances',
        'gross',
        'nssf_employee',
        'nssf_employer',
        'psssf',
        'paye',
        'sdl',
        'wcf',
        'absence_deduction',
        'loan_deduction',
        'overtime_payment',
        'total_deductions',
        'net_pay',
        'employer_cost',
        'total_payroll_cost',
        'note',
        'bonus',
        'calendar_days',
        'absent_days',
        'paid_days',
        'daily_rate',
        'heslb_deduction',
        'heslb_balance_before',
        'heslb_balance_after',
        'previous_net_pay',
        'net_variation',
        'gross_variation',
    ];
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }
    public function workpoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }
}