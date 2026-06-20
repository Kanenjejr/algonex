<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;
    protected $table = 'leave_requests';
    protected $fillable = ['user_id','company_id','work_point_id','leave_type','start_date','end_date','reason','status','approved_by','admin_note'];

    public function user() { return $this->belongsTo(User::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function company() { return $this->belongsTo(CompanySite::class, 'company_id'); }
    public function workpoint() { return $this->belongsTo(WorkPoint::class, 'work_point_id'); }
}
