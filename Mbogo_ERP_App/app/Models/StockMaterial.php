<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMaterial extends Model
{
    use HasFactory;
    protected $table = 'stock_materials';
    protected $fillable = [
        'company_id',
        'work_point_id',
        'raw_id',
        'rcv_no_bags',
        'rcv_bag_size',
        'rcv_tones',
        'iss_no_bags',
        'iss_bag_size',
        'iss_tones',
    ];
    public function raw()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_id');
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
