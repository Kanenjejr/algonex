<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffDocument extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','company_id','work_point_id','title','file_path','file_name','mime_type','size','status'];

    public function user() { return $this->belongsTo(User::class); }
    public function company() { return $this->belongsTo(CompanySite::class, 'company_id'); }
    public function workpoint() { return $this->belongsTo(WorkPoint::class, 'work_point_id'); }
}