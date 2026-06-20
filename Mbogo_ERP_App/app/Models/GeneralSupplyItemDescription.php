<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSupplyItemDescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'description_name',
        'unit_name',
        'status',
        'created_by',
        'updated_by',
    ];

    public function item()
    {
        return $this->belongsTo(GeneralSupplyItem::class, 'item_id');
    }

    public function receivings()
    {
        return $this->hasMany(GeneralSupplyReceiving::class, 'item_description_id');
    }

    public function stocks()
    {
        return $this->hasMany(GeneralSupplyStock::class, 'item_description_id');
    }
}