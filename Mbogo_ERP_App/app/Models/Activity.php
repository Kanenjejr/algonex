<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assigned_to',

        'company_id',
        'work_point_id',

        'opportunity_id',
        'cstm_id',
        'cstm_order_id',

        'purchase_id',
        'sales_id',
        'stock_id',

        'type',
        'module',

        'subject',
        'body',

        'status',

        'account_code',
        'account_name',

        'activity_date',
        'due_at',
    ];

    // ================= RELATIONSHIPS =================

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }

    public function workPoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class, 'opportunity_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'cstm_id');
    }
}