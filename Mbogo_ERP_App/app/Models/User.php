<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Permissions\HasPermissionsTrait;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasPermissionsTrait;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'name',
        'gender',
        'phone_No',
        'email',
        'password',
        'status',
        'image',
        'role',
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'gross_salary',
        'accName',
        'accNo',
        'nssfNo',
        'wcfNo',
        'NHIF',
        'TIN',
        'st_sign'
    ];

    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }

    public function comp_unit()
    {
        return $this->belongsTo(Company_unit::class, 'comp_unit_id');
    }

    public function workpoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}