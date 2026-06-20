<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebitNote extends Model
{
    protected $fillable = [
        'dn_no',
        'dn_date',
        'supplier_id',
        'supplier_invoice_id',
        'company_id',
        'work_point_id',
        'account_code',
        'account_name',
        'total_amount',
        'applied_amount',
        'remaining_amount',
        'status',
        'reason',
        'remarks',
        'created_by',
        'updated_by'
    ];

    public function supplier()
    {
        return $this->belongsTo(Vendor::class, 'supplier_id');
    }

    public function invoice()
    {
        return $this->belongsTo(SupplierInvoice::class, 'supplier_invoice_id');
    }

    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }

    public function workPoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }
}