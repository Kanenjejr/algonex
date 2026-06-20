<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroLoanProduct extends Model
{
    use HasFactory;

    protected $table = 'micro_loan_products';

    protected $fillable = [
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'loan_category_id',
        'product_name',
        'min_amount',
        'max_amount',
        'min_duration_months',
        'max_duration_months',
        'default_interest_rate',
        'interest_method',
        'default_penalty_percent_per_day',
        'default_penalty_basis',
        'default_reminder_charge',
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

    public function category()
    {
        return $this->belongsTo(MicroLoanCategory::class, 'loan_category_id');
    }

    public function applications()
    {
        return $this->hasMany(MicroLoanApplication::class, 'loan_product_id');
    }
}