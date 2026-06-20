<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'proforma_id',
        'customer_id',

        'delivery_no',
        'waybill_no',
        'delivery_note_no',

        // Packing List / Customs Manifest fields
        'customs_manifest_no',
        'export_reference_no',

        'delivery_date',

        'delivery_type',
        'tracking_no',
        'transport_owner',
        'transport_mode',
        'transporter_name',

        'driver_name',
        'driver_phone',
        'vehicle_no',
        'truck2_registration_no',
        'trailer_registration_no',
        'container_no',
        'container2_no',
        'container3_no',

        'origin',
        'destination',
        'dispatch_date',
        'expected_delivery_date',
        'actual_delivery_date',

        'receiver_name',
        'receiver_signature',
        'delivered_at',
        'customer_accepted_at',
        'customer_accepted_by',

        'permit_no',
        'storage_type',
        'approved_qty',
        'total_gross_weight',
        'safety_officer',
        'escort_officer',
        'authority',
        'clearing_agent',
        'bill_of_entry_no',
        'exit_entry_no',

        'delivery_income_amount',
        'delivery_income_currency',
        'delivery_income_exchange_rate',
        'delivery_payment_method',
        'delivery_payment_account_id',
        'delivery_service_income_account_id',
        'delivery_income_transaction_group',

        'status',
        'approval_status',
        'delivery_status',
        'approved_at',
        'approved_by',
        'approval_comment',
        'locked',

        'notes',
        'company_id',
        'business_unit_id',
        'work_point_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'dispatch_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'delivered_at' => 'datetime',
        'customer_accepted_at' => 'datetime',
        'approved_at' => 'datetime',
        'approved_qty' => 'decimal:4',
        'delivery_income_amount' => 'decimal:2',
        'delivery_income_exchange_rate' => 'decimal:6',
        'locked' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function proforma()
    {
        return $this->belongsTo(Proforma::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

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

    public function paymentAccount()
    {
        return $this->belongsTo(AccntSubchart::class, 'delivery_payment_account_id');
    }

    public function serviceIncomeAccount()
    {
        return $this->belongsTo(AccntSubchart::class, 'delivery_service_income_account_id');
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

    public function customerAcceptedBy()
    {
        return $this->belongsTo(User::class, 'customer_accepted_by');
    }

    public function isClosed()
    {
        return $this->locked ||
            $this->status === 'closed' ||
            $this->delivery_status === 'closed' ||
            !empty($this->customer_accepted_at);
    }
}