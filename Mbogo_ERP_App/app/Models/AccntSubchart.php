<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccntSubchart extends Model
{
    use HasFactory;

    protected $table = 'accnt_subcharts';

    protected $fillable = [
        'company_id',
        'work_point_id',
        'accnt_chart_id',
        'SubCode',
        'SubDescription',
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

    public function masterChart()
    {
        return $this->belongsTo(AccntChart::class, 'accnt_chart_id');
    }
}