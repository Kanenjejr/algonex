<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DrillingBlasting extends Model
{
    protected $table = 'drilling_blastings';

    protected $fillable = [
        'record_no','record_date','company_id','comp_unit_id','work_point_id','customer_name','project_site',
        'period_from','period_to','blasts_conducted','total_holes_charged','explosive_type','explosive_qty',
        'detonators_qty','detonating_cord_m','booster_qty','total_rock_blasted','rock_unit','authorized_blaster',
        'remarks','status','created_by','updated_by'
    ];

    protected $casts = [
        'record_date' => 'date',
        'period_from' => 'date',
        'period_to' => 'date',
        'blasts_conducted' => 'integer',
        'total_holes_charged' => 'integer',
        'explosive_qty' => 'decimal:2',
        'detonators_qty' => 'decimal:2',
        'detonating_cord_m' => 'decimal:2',
        'booster_qty' => 'decimal:2',
        'total_rock_blasted' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(\App\Models\CompanySite::class, 'company_id');
    }

    public function companyUnit()
    {
        return $this->belongsTo(\App\Models\Company_unit::class, 'comp_unit_id');
    }

    public function workPoint()
    {
        return $this->belongsTo(\App\Models\WorkPoint::class, 'work_point_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
