<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroLoanAttachment extends Model
{
    use HasFactory;

    protected $table = 'micro_loan_attachments';

    protected $fillable = [
        'loan_application_id',
        'repayment_id',
        'attachment_type',
        'file_name',
        'file_path',
        'file_ext',
        'uploaded_by_name',
        'uploaded_by',
        'status',
    ];

    public function application()
    {
        return $this->belongsTo(MicroLoanApplication::class, 'loan_application_id');
    }

    public function repayment()
    {
        return $this->belongsTo(MicroLoanRepayment::class, 'repayment_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}