<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company_unit extends Model
{
    use HasFactory;

    protected $table = 'company_units';

    protected $fillable = [
        'company_id',
        'unit_code',
        'unit_name',
        'location',
        'district',
        'city',
        'user_id',
        'phone_No',
    ];

    public function company()
    {
        return $this->belongsTo(
            CompanySite::class,
            'company_id'
        );
    }
}