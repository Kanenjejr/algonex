<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffEducation extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','company_id','work_point_id','level','institution','field_of_study','year_completed','status'];

    public function user() { return $this->belongsTo(User::class); }
    public function company() { return $this->belongsTo(CompanySite::class, 'company_id'); }
    public function workpoint() { return $this->belongsTo(WorkPoint::class, 'work_point_id'); }
}