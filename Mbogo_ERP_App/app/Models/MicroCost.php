<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroCost extends Model
{
    use HasFactory;

    protected $table = 'micro_costs';

    protected $fillable = [
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'loan_application_id',
        'cost_type',
        'cost_date',
        'cost_name',
        'amount',
        'remarks',
        'recorded_by',
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

    public function application()
    {
        return $this->belongsTo(MicroLoanApplication::class, 'loan_application_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}