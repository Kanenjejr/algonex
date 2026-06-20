<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroLoanPenalty extends Model
{
    use HasFactory;

    protected $table = 'micro_loan_penalties';

    protected $fillable = [
        'loan_application_id',
        'penalty_date',
        'days_overdue',
        'base_amount',
        'penalty_percent_per_day',
        'penalty_basis',
        'penalty_amount',
        'remarks',
        'created_by',
        'status',
    ];

    public function application()
    {
        return $this->belongsTo(MicroLoanApplication::class, 'loan_application_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}