<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroLoanCollateral extends Model
{
    use HasFactory;

    protected $table = 'micro_loan_collaterals';

    protected $fillable = [
        'loan_application_id',
        'collateral_type',
        'item_name',
        'no_of_items',
        'serial_number',
        'color',
        'original_cost',
        'estimated_value',
        'discounted_value',
        'ownership_notes',
        'status',
    ];

    public function application()
    {
        return $this->belongsTo(MicroLoanApplication::class, 'loan_application_id');
    }
}