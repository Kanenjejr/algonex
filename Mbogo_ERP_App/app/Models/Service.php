<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    protected $table = 'services';



    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'company_id',

        'business_unit_id',

        'work_point_id',

        'service_code',

        'service_name',

        'price',

        'unit',

        'status',
    ];



    /*
    |--------------------------------------------------------------------------
    | COMPANY RELATION
    |--------------------------------------------------------------------------
    */

    public function company()
    {
        return $this->belongsTo(
            Company::class,
            'company_id'
        );
    }



    /*
    |--------------------------------------------------------------------------
    | BUSINESS UNIT RELATION
    |--------------------------------------------------------------------------
    */

    public function businessUnit()
    {
        return $this->belongsTo(
            BusinessUnit::class,
            'business_unit_id'
        );
    }



    /*
    |--------------------------------------------------------------------------
    | WORK POINT RELATION
    |--------------------------------------------------------------------------
    */

    public function workPoint()
    {
        return $this->belongsTo(
            WorkPoint::class,
            'work_point_id'
        );
    }
}