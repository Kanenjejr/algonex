<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_no','payment_date','invoice_id','proforma_id','customer_id','company_id','business_unit_id','work_point_id',
        'amount','currency','exchange_rate','payment_method','payment_account_id','receipt_no','receipt_attachment','notes',
        'status','transaction_group','approved_at','approved_by','approval_comment','locked','created_by','updated_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'approved_at' => 'datetime',
        'locked' => 'boolean',
    ];

    public function invoice(){ return $this->belongsTo(Invoice::class); }
    public function proforma(){ return $this->belongsTo(Proforma::class); }
    public function customer(){ return $this->belongsTo(Customer::class); }
    public function company(){ return $this->belongsTo(CompanySite::class, 'company_id'); }
    public function businessUnit(){ return $this->belongsTo(Company_unit::class, 'business_unit_id'); }
    public function workPoint(){ return $this->belongsTo(WorkPoint::class, 'work_point_id'); }
    public function paymentAccount(){ return $this->belongsTo(AccntSubchart::class, 'payment_account_id'); }
    public function approver(){ return $this->belongsTo(User::class, 'approved_by'); }

    public function isApproved(){ return $this->status === 'approved' || !empty($this->approved_at); }
}
