<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [

        'customer_code',
        'customer_name',
        'customer_type',
        'description',

        'account_id',

        'phone',
        'email',
        'address',

        'tin_number',
        'vrn',

        'country',
        'destination',

        'credit_limit',
        'opening_balance',

        'company_id',
        'comp_unit_id',
        'work_point_id',

        'created_by',
        'updated_by',

        'status',
    ];

    // ================= RELATIONSHIPS =================

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'cstm_id');
    }

    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }

    public function businessUnit()
    {
        return $this->belongsTo(Company_unit::class, 'comp_unit_id');
    }

    public function workPoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }

    public function account()
    {
        return $this->belongsTo(AccntSubchart::class, 'account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ================= SCOPES =================

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'Inactive');
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByBusinessUnit($query, $unitId)
    {
        return $query->where('comp_unit_id', $unitId);
    }

    public function scopeByWorkPoint($query, $workPointId)
    {
        return $query->where('work_point_id', $workPointId);
    }
}