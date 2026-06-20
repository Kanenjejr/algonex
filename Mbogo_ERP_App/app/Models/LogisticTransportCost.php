<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogisticTransportCost extends Model
{
    use HasFactory;

    protected $table = 'logistic_transport_costs';

    protected $fillable = [
        'cost_no','transport_order_id','cost_date','vehicle_source','hire_cost','fuel_cost',
        'driver_allowance','escort_allowance','loading_cost','other_cost','total_cost',
        'profit','company_id','comp_unit_id','work_point_id','status','remarks','created_by','updated_by'
    ];

    protected $casts = [
        'cost_date' => 'date',
        'hire_cost' => 'decimal:2',
        'fuel_cost' => 'decimal:2',
        'driver_allowance' => 'decimal:2',
        'escort_allowance' => 'decimal:2',
        'loading_cost' => 'decimal:2',
        'other_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    public function order(){ return $this->belongsTo(LogisticTransportOrder::class, 'transport_order_id'); }
    public function company(){ return $this->belongsTo(CompanySite::class, 'company_id'); }
    public function compUnit(){ return $this->belongsTo(Company_unit::class, 'comp_unit_id'); }
    public function workPoint(){ return $this->belongsTo(WorkPoint::class, 'work_point_id'); }
}
