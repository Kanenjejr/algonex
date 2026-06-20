<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HiredEquipment extends Model
{
    use HasFactory;
      protected $table = 'hired_equipment';

    protected $fillable = [
        'user_id','company_id','work_point_id','cstm_id',
        'Model','EqpmntNo','OperatorName','PaymentPerDay','Status'
    ];

    public function user() { return $this->belongsTo(User::class,'user_id'); }
    public function company() { return $this->belongsTo(CompanySite::class, 'company_id'); }
    public function workpoint() { return $this->belongsTo(WorkPoint::class, 'work_point_id'); }
    public function cstm() { return $this->belongsTo(CstmSply::class, 'cstm_id'); }

    public function workings()
    {
        return $this->hasMany(HiredEquipmentWorking::class, 'hired_equipment_id');
    }
}