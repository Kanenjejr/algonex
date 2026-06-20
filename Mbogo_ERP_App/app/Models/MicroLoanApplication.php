<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroLoanApplication extends Model
{
    use HasFactory;

    protected $table = 'micro_loan_applications';

    protected $fillable = [
        'application_no',
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'applicant_id',
        'loan_category_id',
        'loan_product_id',
        'created_by',
        'application_date',
        'amount_applied',
        'approved_amount',
        'project_cost',
        'own_contribution',
        'loan_period_months',
        'monthly_repayment',
        'interest_rate',
        'interest_method',
        'penalty_percent_per_day',
        'penalty_basis',
        'reminder_charge',
        'sms_token_cost',
        'expected_start_date',
        'expected_end_date',
        'cashout_date',
        'purpose',
        'notes',
        'verification_status',
        'verified_by',
        'verified_at',
        'verification_remarks',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_remarks',
        'disbursement_status',
        'loan_status',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }

    public function companyUnit()
    {
        return $this->belongsTo(Company_unit::class, 'comp_unit_id');
    }

    public function workPoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }

    public function applicant()
    {
        return $this->belongsTo(MicroLoanApplicant::class, 'applicant_id');
    }

    public function category()
    {
        return $this->belongsTo(MicroLoanCategory::class, 'loan_category_id');
    }

    public function product()
    {
        return $this->belongsTo(MicroLoanProduct::class, 'loan_product_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function guarantors()
    {
        return $this->hasMany(MicroLoanGuarantor::class, 'loan_application_id');
    }

    public function collaterals()
    {
        return $this->hasMany(MicroLoanCollateral::class, 'loan_application_id');
    }

    public function attachments()
    {
        return $this->hasMany(MicroLoanAttachment::class, 'loan_application_id');
    }

    public function disbursements()
    {
        return $this->hasMany(MicroLoanDisbursement::class, 'loan_application_id');
    }

    public function repayments()
    {
        return $this->hasMany(MicroLoanRepayment::class, 'loan_application_id');
    }

    public function penalties()
    {
        return $this->hasMany(MicroLoanPenalty::class, 'loan_application_id');
    }

    public function reminders()
    {
        return $this->hasMany(MicroLoanReminder::class, 'loan_application_id');
    }

    public function costs()
    {
        return $this->hasMany(MicroCost::class, 'loan_application_id');
    }

    public function otherIncome()
    {
        return $this->hasMany(MicroOtherIncome::class, 'loan_application_id');
    }
}