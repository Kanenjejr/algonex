<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Followup extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'followups';

    protected $fillable = [
        'customer_id',
        'followup_date',
        'priority',
        'notes',
        'status',
        'user_id',
        'company_id',
        'work_point_id'
    ];

    protected $casts = [
        'followup_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
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