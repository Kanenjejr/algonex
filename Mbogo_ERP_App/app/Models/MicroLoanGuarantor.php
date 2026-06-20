<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroLoanGuarantor extends Model
{
    use HasFactory;

    protected $table = 'micro_loan_guarantors';

    protected $fillable = [
        'loan_application_id',
        'relation_type',
        'full_name',
        'phone_no',
        'relationship',
        'email',
        'work_email',
        'branch',
        'status',
    ];

    public function application()
    {
        return $this->belongsTo(MicroLoanApplication::class, 'loan_application_id');
    }
}