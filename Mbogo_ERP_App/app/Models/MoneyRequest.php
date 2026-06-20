<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoneyRequest extends Model
{
    use HasFactory;

    protected $table = 'money_requests';

    protected $fillable = [
        'company_id',
        'company_unit_id',
        'work_point_id',
        'User_id',
        'account_id',
        'sub_account_id',
        'department_id',
        'section_id',
        'RequestNo',
        'RequestDate',
        'PayeeName',
        'PayeeContact',
        'Description',
        'total_amount',
        'Status',
        'verified_by',
        'verified_at',
        'verified_comment',
        'approved_by',
        'approved_at',
        'approval_comment',
        'approved_amount',
        'Payment_mode',
        'cashed_by',
        'cashed_at',
        'payment_vocher_no',
        'cashier_comment',
        'rejected_by',
        'rejected_at',
        'rejection_comment',
        'retired_by',
        'retired_at',
        'retirement_docs',
        'retirement_comment',
        'returned_amount',
        'remarks',
    ];

    protected $casts = [
        'RequestDate' => 'date',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'cashed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'retired_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'returned_amount' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }

    public function unit()
    {
        return $this->belongsTo(Company_unit::class, 'company_unit_id');
    }

    public function workpoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'User_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashed_by');
    }

    public function retreater()
    {
        return $this->belongsTo(User::class, 'retired_by');
    }

    public function account()
    {
        return $this->belongsTo(AccntChart::class, 'account_id');
    }

    public function subAccount()
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

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $globalRoles = ['Admin', 'CEO', 'Managing Director (MD)', 'Accountant Director (DAF)', 'Chief Accountant', 'Admin-Developer'];

        if ($user->can('View-All-MoneyRequest') || in_array($user->role, $globalRoles, true)) {
            return $query;
        }

        if ($user->can('View-Company-MoneyRequest') || $user->role === 'Company Manager') {
            return $query->where('company_id', $user->company_id);
        }

        if ($user->can('View-Unit-MoneyRequest') || $user->role === 'Unit Manager') {
            return $query->where('company_id', $user->company_id)
                         ->where('company_unit_id', $user->comp_unit_id);
        }

        return $query->where('User_id', $user->id);
    }
}