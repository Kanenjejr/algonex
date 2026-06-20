<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proforma extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'proformas';

    protected $fillable = [
        'proforma_no','customer_id','company_id','business_unit_id','work_point_id',
        'subtotal','vat','total','invoice_type','status','payment_status','paid_amount',
        'created_by','updated_by','bank_id','account_number','swift_code','branch',
        'accounting_transaction_group','approved_at','approved_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'vat' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function customer(){ return $this->belongsTo(Customer::class, 'customer_id'); }
    public function items(){ return $this->hasMany(ProformaItem::class, 'proforma_id'); }
    public function subchart(){ return $this->belongsTo(AccntSubchart::class, 'subchart_id'); }
    public function bank(){ return $this->belongsTo(AccntSubchart::class, 'bank_id'); }
    public function company(){ return $this->belongsTo(CompanySite::class, 'company_id'); }
    public function businessUnit(){ return $this->belongsTo(Company_unit::class, 'business_unit_id'); }
    public function workPoint(){ return $this->belongsTo(WorkPoint::class, 'work_point_id'); }
    public function creator(){ return $this->belongsTo(User::class, 'created_by'); }
    public function updater(){ return $this->belongsTo(User::class, 'updated_by'); }
    public function approver(){ return $this->belongsTo(User::class, 'approved_by'); }
    public function invoices(){ return $this->hasMany(Invoice::class, 'proforma_id'); }
    public function payments(){ return $this->hasMany(CustomerPayment::class, 'proforma_id'); }

    public function isApproved(){ return strtolower($this->status) === 'approved' || !empty($this->approved_at); }

    public function getBalanceAttribute()
    {
        return max(0, (float)$this->total - (float)$this->paid_amount);
    }
}
