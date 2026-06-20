<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'reference_no',
        'invoice_date',
        'due_date',
        'agreement_date',
        'invoice_type',
        'proforma_id',
        'customer_id',
        'contact_id',
        'company_id',
        'business_unit_id',
        'work_point_id',
        'bank_id',
        'bank_name',
        'account_number',
        'swift_code',
        'branch',
        'currency',
        'exchange_rate',
        'total_tzs',
        'sub_total',
        'vat_rate',
        'vat_inclusive',
        'tax',
        'discount',
        'total',
        'payment_type',
        'paid_amount',
        'balance',
        'payment_status',
        'status',
        'stock_posted',
        'has_delivery',
        'locked',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'agreement_date' => 'date',
        'sub_total' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'total_tzs' => 'decimal:2',
        'vat_inclusive' => 'boolean',
        'stock_posted' => 'boolean',
        'has_delivery' => 'boolean',
        'locked' => 'boolean',
    ];

    public function proforma()
    {
        return $this->belongsTo(Proforma::class, 'proforma_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
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

    public function bank()
    {
        return $this->belongsTo(AccntSubchart::class, 'bank_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function payments()
    {
        return $this->hasMany(CustomerPayment::class, 'invoice_id');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'invoice_id');
    }

    public function isLocked()
    {
        return (bool) $this->locked ||
            $this->payments()->where('status', 'approved')->exists();
    }
}
