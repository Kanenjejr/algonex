<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'business_unit_id',
        'work_point_id',
        'vendor_id',

        'po_no',
        'pi_no',
        'po_date',
        'expected_delivery_date',
        'purchase_type',

        'ship_to',
        'vendor_from',
        'shipping_method',
        'shipping_terms',
        'delivery_point',
        'terms_conditions',
        'remarks',

        'currency',
        'exchange_rate',
        'vat_rate',

        'sub_total',
        'vat_amount',
        'discount',
        'total_amount',
        'total_tzs',

        'payment_status',
        'amount_paid',
        'balance',
        'payment_method',
        'cheque_no',
        'payment_reference',
        'payment_attachment',
        'payment_date',

        'receive_status',
        'received_date',
        'received_by',

        'supplier_proforma_attachment',
        'supplier_invoice_attachment',
        'delivery_note_attachment',

        'account_code',
        'account_name',
        'accounting_transaction_group',

        'status',
        'approved_at',
        'approved_by',

        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'po_date' => 'date',
        'expected_delivery_date' => 'date',
        'payment_date' => 'date',
        'received_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }

    public function businessUnit()
    {
        return $this->belongsTo(Company_unit::class, 'business_unit_id');
    }

    public function workPoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}