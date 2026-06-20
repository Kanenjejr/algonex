<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackedPrd extends Model
{
    use HasFactory;
    protected $table = 'packed_prds';

    protected $fillable = [
        'pck_date',
        'company_id',
        'work_point_id',
        'prd_id',
        'pck_qnty',
        'pck_unit',
        'user_id',
        'status',
    ];
    // relations
    public function product()
    {
        return $this->belongsTo(Product::class, 'prd_id');
    }
    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }
    public function workpoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}