<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opportunity extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'opportunities';

    protected $fillable = [
        'opportunity_name',

        'user_id',
        'assigned_to',

        'company_id',
        'business_unit_id',
        'work_point_id',

        'cstm_id',

        'estimated_value',
        'close_expected',

        'stage',
        'status',

        'description',
    ];

    // ================= RELATIONSHIPS =================

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'cstm_id');
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}