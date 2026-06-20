<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrdPrice extends Model
{
    use HasFactory;
     protected $table = 'prd_prices';

    protected $fillable = [
        'company_id',
        'work_point_id',
        'Product_id',   // note capitalization from migration
        'User_id',
        'RawPrice',
        'Status',
    ];
    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }
    public function workpoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'Product_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'User_id');
    }
}
