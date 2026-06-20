<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_reports';

    protected $fillable = [
        'report_name',
        'report_type',
        'start_date',
        'end_date',
        'total_customers',
        'total_leads',
        'total_sales',
        'total_payments',
        'total_debts',
        'total_opportunities',
        'total_campaigns',
        'generated_by',
        'company_id',
        'work_point_id',
        'remarks',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_sales' => 'decimal:2',
        'total_payments' => 'decimal:2',
        'total_debts' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function workPoint()
    {
        return $this->belongsTo(WorkPoint::class);
    }

    /*
    |--------------------------------------------------------------------------
    | REPORT TYPES
    |--------------------------------------------------------------------------
    */

    public static function reportTypes()
    {
        return [
            'sales_report',
            'customer_report',
            'lead_report',
            'campaign_report',
            'debt_report',
            'payment_report',
            'opportunity_report',
            'pipeline_report',
            'activity_report'
        ];
    }
}