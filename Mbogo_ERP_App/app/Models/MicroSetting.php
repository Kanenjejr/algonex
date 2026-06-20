<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroSetting extends Model
{
    use HasFactory;

    protected $table = 'micro_settings';

    protected $fillable = [
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'sms_token_cost',
        'default_reminder_charge',
        'default_penalty_basis',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }

    public function companyUnit()
    {
        return $this->belongsTo(Company_unit::class, 'comp_unit_id');
    }

    public function workPoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }
}