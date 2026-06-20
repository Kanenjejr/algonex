<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'campaigns';

    protected $fillable = [
        'name',
        'description',
        'type',
        'discount',
        'budget',
        'revenue_generated',
        'discount_given',
        'customer_type',

        'start_date',
        'end_date',

        'company_id',
        'business_unit_id',
        'work_point_id',

        'company_code',
        'company_name',

        'business_code',
        'business_name',

        'location_code',
        'location_name',

        'status',
    ];

    // ================= RELATIONSHIPS =================

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

    // ================= CHECK IF CAMPAIGN IS ACTIVE =================

    public function isActive()
    {
        return now()->between($this->start_date, $this->end_date)
            && $this->status === 'active';
    }

    // ================= CHECK IF CAMPAIGN APPLIES TO CUSTOMER =================

    public function appliesToCustomer($customer)
    {
        return $this->customer_type === 'all'
            || $customer->customer_type === $this->customer_type;
    }

    // ================= PROFIT =================

    public function getProfit()
    {
        return ($this->revenue_generated ?? 0) - ($this->discount_given ?? 0);
    }

    // ================= ROI =================

    public function getROI()
    {
        if (!$this->budget || $this->budget == 0) {
            return 0;
        }

        return ($this->getProfit() / $this->budget) * 100;
    }
}