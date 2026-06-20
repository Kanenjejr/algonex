<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrdStock extends Model
{
    use HasFactory;
    protected $table = 'prd_stocks';

    protected $fillable = [
        'company_id',
        'work_point_id',
        'prd_id',
        'stck_unit',
        'avlb_qnty',
        'issd_qnty',
    ];
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
}
