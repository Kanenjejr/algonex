<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'work_point_id',
        'cstm_id',
        'first_name',
        'last_name',
        'job_title',
        'phone',
        'email',
        'address',
        'notes',
        'status',
        'created_by',
        'updated_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }

    public function workPoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'cstm_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }


    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function account()
{
    return $this->belongsTo(Account::class, 'account_id');
}
}