<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'company_id',
        'vendor_name',
        'vendor_code',
        'phone_no',
        'email',
        'address',
        'tin_no',
        'account_code',
        'account_name',
        'status',
        'created_by',
        'updated_by',
    ];

    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
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