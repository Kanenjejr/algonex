<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerLedger extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customer_ledgers';

    protected $fillable = [
        'customer_id',
        'invoice_id',
        'payment_id',
        'invoice_amount',
        'paid_amount',
        'balance',
        'status',
        'transaction_date',
        'remarks',
        'user_id',
        'company_id',
        'work_point_id'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'invoice_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function workPoint()
    {
        return $this->belongsTo(WorkPoint::class);
    }
}