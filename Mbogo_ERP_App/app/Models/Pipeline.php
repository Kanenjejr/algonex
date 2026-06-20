<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pipeline extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pipelines';

    protected $fillable = [
        'customer_id',
        'lead_id',
        'opportunity_id',
        'stage',
        'expected_value',
        'probability',
        'expected_close_date',
        'status',
        'remarks',
        'user_id',
        'company_id',
        'work_point_id'
    ];

    protected $casts = [
        'expected_close_date' => 'date',
        'expected_value' => 'decimal:2',
        'probability' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
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

    /*
    |--------------------------------------------------------------------------
    | SALES STAGES
    |--------------------------------------------------------------------------
    */

    public static function stages()
    {
        return [
            'lead',
            'opportunity',
            'quotation',
            'negotiation',
            'approval',
            'invoice',
            'payment',
            'delivery',
            'completed',
            'cancelled'
        ];
    }
}