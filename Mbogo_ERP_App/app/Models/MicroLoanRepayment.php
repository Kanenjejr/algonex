<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroLoanRepayment extends Model
{
    use HasFactory;

    protected $table = 'micro_loan_repayments';

    protected $fillable = [
        'loan_application_id',
        'repayment_date',
        'amount_paid',
        'principal_paid',
        'interest_paid',
        'penalty_paid',
        'reminder_charge_paid',
        'recoverable_cost_paid',
        'payment_method',
        'reference_no',
        'remarks',
        'received_by',
        'status',
    ];

    public function application()
    {
        return $this->belongsTo(MicroLoanApplication::class, 'loan_application_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function attachments()
    {
        return $this->hasMany(MicroLoanAttachment::class, 'repayment_id');
    }
}