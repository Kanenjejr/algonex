<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'accounts'; 

    protected $fillable = [
        'account_code',
        'account_name',
        'account_type',
        'company_id'
    ];

    /**
     * ================= RELATIONSHIPS =================
     */

    // 🔹 Account belongs to Company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // 🔹 Account used by Customers
    public function customers()
    {
        return $this->hasMany(Customer::class, 'account_id');
    }

    /**
     * ================= ACCESSORS =================
     */

    //  Display nzuri kwenye dropdown
    public function getFullNameAttribute()
    {
        return $this->account_code . ' - ' . $this->account_name;
    }
}