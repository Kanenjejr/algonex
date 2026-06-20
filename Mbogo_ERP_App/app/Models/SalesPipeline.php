<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesPipeline extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales_pipelines';

    protected $fillable = [
        'pipeline_code',
        'title',
        'description',

        'customer_id',
        'lead_id',
        'opportunity_id',
        'invoice_id',
        'payment_id',

        'company_id',
        'business_unit_id',
        'work_point_id',

        'stage',
        'status',

        'expected_value',
        'actual_value',
        'probability',

        'expected_close_date',
        'closed_date',

        'assigned_to',
        'created_by',
        'updated_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class, 'opportunity_id');
    }

    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }

    public function businessUnit()
    {
        return $this->belongsTo(Company_unit::class, 'business_unit_id');
    }

    public function workPoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}