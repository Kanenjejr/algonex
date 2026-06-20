<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccntChart extends Model
{
    use HasFactory;

    protected $table = 'accnt_charts';

    protected $fillable = [
        'company_id',
        'work_point_id',
        'AccCode',
        'AccDescription',
        'AccType',
        'Status',
    ];

    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }

    public function workpoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }

    public function subcharts()
    {
        return $this->hasMany(AccntSubchart::class, 'accnt_chart_id');
    }
}