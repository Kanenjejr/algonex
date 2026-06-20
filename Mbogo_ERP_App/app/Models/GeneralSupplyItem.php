<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSupplyItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'item_code',
        'status',
        'created_by',
        'updated_by',
    ];

    public function descriptions()
    {
        return $this->hasMany(GeneralSupplyItemDescription::class, 'item_id');
    }

    public function receivings()
    {
        return $this->hasMany(GeneralSupplyReceiving::class, 'item_id');
    }

    public function stocks()
    {
        return $this->hasMany(GeneralSupplyStock::class, 'item_id');
    }
}