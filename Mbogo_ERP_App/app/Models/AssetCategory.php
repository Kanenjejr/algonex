<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    use HasFactory;
    protected $table='asset_categories';
    protected $fillable = [
        'name','code','description','depreciation_rate','status',
        'user_id','company_id','work_point_id'
    ];

    // relations
    public function user() { return $this->belongsTo(User::class); }
    public function companySite() { return $this->belongsTo(CompanySite::class, 'company_id'); }
    public function workPoint() { return $this->belongsTo(WorkPoint::class, 'work_point_id'); }
    public function assets() { return $this->hasMany(AssetTransaction::class, 'asset_category_id'); }
}
