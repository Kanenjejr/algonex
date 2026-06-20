<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeslbLoanPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'heslb_loan_id','payroll_id','payroll_line_id','user_id','period','amount',
        'balance_before','balance_after','status','notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function loan() { return $this->belongsTo(HeslbLoan::class, 'heslb_loan_id'); }
    public function payroll() { return $this->belongsTo(Payroll::class); }
    public function line() { return $this->belongsTo(PayrollLine::class, 'payroll_line_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
