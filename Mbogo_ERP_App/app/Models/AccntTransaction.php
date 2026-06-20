<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class AccntTransaction extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'accnt_transactions';
    protected $fillable = [
        'pcv_no',
        'transaction_group',
        'trans_date',
        'reference',
        'check_no',
        'request_no',
        'requisition_id',
        'category',
        'exchange_rate',
        'currency',
        'memo',
        'payee',
        'user_id',
        'company_id',
        'work_point_id',
        'account_id',
        'sub_account_id',
        'department_id',
        'section_id',
        'type',
        'amount',
        'source_amount',
        'imported_from_excel',
        'Status',
        'verified',
        'verified_by',
        'verified_at',
        'verification_comment',
        'approved',
        'approved_by',
        'approved_at',
        'approval_comment',
    ];
    protected $casts = [
        'trans_date' => 'date',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
        'source_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'imported_from_excel' => 'boolean',
    ];
    public function account()
    {
        return $this->belongsTo(AccntChart::class, 'account_id');
    }
    public function subaccount()
    {
        return $this->belongsTo(AccntSubchart::class, 'sub_account_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
    public function workpoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function requisition()
    {
        return $this->belongsTo(MoneyRequest::class, 'requisition_id');
    }
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $globalRoles = ['Admin', 'CEO', 'Managing Director (MD)', 'Accountant Director (DAF)', 'Chief Accountant', 'Admin-Developer'];
        if ($user->can('View-All-Accounting-Transactions') || in_array($user->role, $globalRoles, true)) {
            return $query;
        }
        if ($user->can('View-Company-Accounting-Transactions')) {
            return $query->where('company_id', $user->company_id);
        }
        if ($user->can('View-Unit-Accounting-Transactions')) {
            return $query->where('company_id', $user->company_id)
                ->whereHas('workpoint', function ($q) use ($user) {
                    $q->where('comp_unit_id', $user->comp_unit_id);
                });
        }
        return $query->where('company_id', $user->company_id)
            ->where('work_point_id', $user->work_point_id);
    }
}