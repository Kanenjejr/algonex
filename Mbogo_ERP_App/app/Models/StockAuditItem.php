<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAuditItem extends Model
{
    use HasFactory;

    protected $table = 'stock_audit_items';

    protected $fillable = [
        'stock_audit_id',
        'item_type',
        'item_id',
        'system_qty',
        'physical_qty',
        'counted_qty',
        'variance_qty',
        'remarks',
    ];

    protected $casts = [
        'system_qty'   => 'decimal:4',
        'physical_qty' => 'decimal:4',
        'counted_qty'  => 'decimal:4',
        'variance_qty' => 'decimal:4',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function audit()
    {
        return $this->belongsTo(StockAudit::class, 'stock_audit_id');
    }

    public function generalSupplyItem()
    {
        return $this->belongsTo(GeneralSupplyItem::class, 'item_id');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::saving(function ($item) {
            $systemQty = (float) ($item->system_qty ?? 0);
            $physicalQty = (float) ($item->physical_qty ?? 0);

            $item->counted_qty = $physicalQty;
            $item->variance_qty = $physicalQty - $systemQty;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Accessors
    |--------------------------------------------------------------------------
    */

    public function getItemModelAttribute()
    {
        if ($this->item_type === 'GeneralSupply') {
            return GeneralSupplyItem::find($this->item_id);
        }

        if ($this->item_type === 'RawMaterial') {
            return RawMaterial::find($this->item_id);
        }

        if ($this->item_type === 'Product') {
            return Product::find($this->item_id);
        }

        return null;
    }

    public function getItemNameAttribute()
    {
        if ($this->item_type === 'GeneralSupply') {
            return optional(GeneralSupplyItem::find($this->item_id))->item_name ?? 'N/A';
        }

        if ($this->item_type === 'RawMaterial') {
            return optional(RawMaterial::find($this->item_id))->material_name ?? 'N/A';
        }

        if ($this->item_type === 'Product') {
            return optional(Product::find($this->item_id))->product_name ?? 'N/A';
        }

        return 'N/A';
    }

    public function getItemCodeAttribute()
    {
        if ($this->item_type === 'GeneralSupply') {
            return optional(GeneralSupplyItem::find($this->item_id))->item_code ?? '';
        }

        if ($this->item_type === 'RawMaterial') {
            return optional(RawMaterial::find($this->item_id))->material_code ?? '';
        }

        return '';
    }

    public function getItemUnitAttribute()
    {
        if ($this->item_type === 'RawMaterial') {
            return optional(RawMaterial::find($this->item_id))->unit_name ?? '';
        }

        if ($this->item_type === 'Product') {
            return optional(Product::find($this->item_id))->product_size ?? '';
        }

        return '';
    }

    public function getItemTypeLabelAttribute()
    {
        switch ($this->item_type) {
            case 'GeneralSupply':
                return 'General Supply';

            case 'RawMaterial':
                return 'Raw Material';

            case 'Product':
                return 'Product';

            default:
                return 'Unknown';
        }
    }

    public function getVarianceTypeAttribute()
    {
        $variance = (float) $this->variance_qty;

        if ($variance > 0) {
            return 'gain';
        }

        if ($variance < 0) {
            return 'loss';
        }

        return 'match';
    }

    public function getVarianceLabelAttribute()
    {
        $variance = (float) $this->variance_qty;

        if ($variance > 0) {
            return 'Gain';
        }

        if ($variance < 0) {
            return 'Loss';
        }

        return 'Matched';
    }

    public function getVarianceBadgeClassAttribute()
    {
        $variance = (float) $this->variance_qty;

        if ($variance > 0) {
            return 'success';
        }

        if ($variance < 0) {
            return 'danger';
        }

        return 'primary';
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForAudit($query, $auditId)
    {
        return $query->where('stock_audit_id', $auditId);
    }

    public function scopeForType($query, $type)
    {
        return $query->where('item_type', $type);
    }

    public function scopeVarianceOnly($query)
    {
        return $query->where('variance_qty', '!=', 0);
    }

    public function scopeLosses($query)
    {
        return $query->where('variance_qty', '<', 0);
    }

    public function scopeGains($query)
    {
        return $query->where('variance_qty', '>', 0);
    }

    public function scopeMatched($query)
    {
        return $query->where('variance_qty', 0);
    }
}