<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Communication extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'communications';

    protected $fillable = [
        'customer_id',
        'type',
        'message',
        'subject',
        'status',
        'user_id',
        'company_id',
        'work_point_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
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