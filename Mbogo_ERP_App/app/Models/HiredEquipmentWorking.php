<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HiredEquipmentWorking extends Model
{
    use HasFactory;
    protected $table = 'hired_equipment_workings';
    protected $fillable = [
        'hired_equipment_id','user_id','company_id','work_point_id',
        'WorkingHours','Minutes','TotalPrice','WorkingDate','TimeIn','TimeOut','PaymentStatus','Status'
    ];
    protected $dates = ['WorkingDate'];
    public function user() { return $this->belongsTo(User::class,'user_id'); }
    public function company() { return $this->belongsTo(CompanySite::class, 'company_id'); }
    public function workpoint() { return $this->belongsTo(WorkPoint::class, 'work_point_id'); }

    public function equipment()
    {
        return $this->belongsTo(HiredEquipment::class, 'hired_equipment_id');
    }
}
