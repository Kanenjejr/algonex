<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffSalaryAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','company_id','work_point_id','period','type','calc_type',
        'rate','amount','status','note'
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function company() { return $this->belongsTo(CompanySite::class, 'company_id'); }
    public function workpoint() { return $this->belongsTo(WorkPoint::class, 'work_point_id'); }
}
