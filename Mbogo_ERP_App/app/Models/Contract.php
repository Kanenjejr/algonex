<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'company_id',
        'comp_unit_id',
        'vendor_id',
        'contract_no',
        'contract_title',
        'start_date',
        'end_date',
        'contract_amount',
        'remarks',
        'status',
        'created_by',
        'updated_by',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}