<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroLoanApplicant extends Model
{
    use HasFactory;

    protected $table = 'micro_loan_applicants';

    protected $fillable = [
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'created_by',
        'applicant_type',
        'full_name',
        'trading_as',
        'national_id_no',
        'passport_no',
        'marital_status',
        'date_of_birth',
        'age',
        'postal_address',
        'permanent_address',
        'personal_email',
        'office_phone',
        'mobile_no',
        'work_email',
        'residence_town',
        'residence_estate',
        'residence_street',
        'house_no',
        'residence_type',
        'building_name',
        'landmark',
        'referred_by',
        'referred_phone',
        'employer',
        'employment_terms',
        'contract_duration_months',
        'employment_date',
        'designation',
        'payroll_no',
        'gross_salary',
        'net_salary',
        'salary_pay_date',
        'department',
        'workstation',
        'branch_name',
        'business_name',
        'business_type',
        'kra_pin',
        'business_tin',
        'business_physical_address',
        'business_town',
        'business_building',
        'nature_of_business',
        'business_premise',
        'business_landmark',
        'annual_turnover',
        'years_in_business',
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications()
    {
        return $this->hasMany(MicroLoanApplication::class, 'applicant_id');
    }
}