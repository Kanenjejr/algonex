<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroLoanDisbursement extends Model
{
    use HasFactory;

    protected $table = 'micro_loan_disbursements';

    protected $fillable = [
        'loan_application_id',
        'disbursement_date',
        'amount_disbursed',
        'channel',
        'reference_no',
        'bank_or_network',
        'remarks',
        'disbursed_by',
        'status',
    ];

    public function application()
    {
        return $this->belongsTo(MicroLoanApplication::class, 'loan_application_id');
    }

    public function disburser()
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }
}