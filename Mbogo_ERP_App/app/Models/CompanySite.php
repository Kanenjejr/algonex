<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySite extends Model
{
    use HasFactory;
    protected $table='company_sites';
    protected $fillable = [
        'company_code',
        'company_name',
        'Type',
        'district',
        'city',
        'TIN',
        'phone_No',
        'logo',
        'user_id',
        'status',
        'stamp',
        'signature',
    ];
}