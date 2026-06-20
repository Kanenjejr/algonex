<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroLoanReminder extends Model
{
    use HasFactory;

    protected $table = 'micro_loan_reminders';

    protected $fillable = [
        'loan_application_id',
        'reminder_date',
        'phone_no',
        'message',
        'sms_charge',
        'delivery_status',
        'provider_reference',
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