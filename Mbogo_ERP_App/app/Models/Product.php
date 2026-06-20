<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        // USER / CREATOR
        'user_id',

        // ERP CORE LINKS
        'company_id',
        'comp_unit_id',
        'work_point_id',

        // PRODUCT DETAILS
        'product_name',
        'product_size',

        // INVENTORY / COSTING
        'avg_cost',
        'total_qty',
        'total_value',
        'selling_price',
        'reorder_level',
        'opening_stock',

        // ACCOUNTING - COGS
        'cogs_account_code',
        'cogs_account_id',

        // ACCOUNTING - INVENTORY
        'inventory_account_code',
        'inventory_account_id',

        // ACCOUNTING - REVENUE
        'revenue_account_code',
        'revenue_account_id',

        // STATUS
        'status',
    ];

    protected $casts = [
        'avg_cost' => 'decimal:2',
        'total_qty' => 'decimal:2',
        'total_value' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'opening_stock' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }

    public function businessUnit()
    {
        return $this->belongsTo(Company_unit::class, 'comp_unit_id');
    }

    public function unit()
    {
        return $this->belongsTo(Company_unit::class, 'comp_unit_id');
    }

    public function workPoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }

    public function ledgers()
    {
        return $this->hasMany(StockLedger::class, 'product_id');
    }

    public function stock()
    {
        return $this->hasOne(ProductStock::class, 'product_id');
    }

    public function prices()
    {
        return $this->hasMany(PrdPrice::class, 'Product_id');
    }

    public function deliveryItems()
    {
        return $this->hasMany(DeliveryItem::class, 'product_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accounting Relationships
    |--------------------------------------------------------------------------
    */

    public function inventoryAccount()
    {
        return $this->belongsTo(Account::class, 'inventory_account_id');
    }

    public function cogsAccount()
    {
        return $this->belongsTo(Account::class, 'cogs_account_id');
    }

    public function revenueAccount()
    {
        return $this->belongsTo(Account::class, 'revenue_account_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getProductCodeAttribute()
    {
        return 'PRD-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getCurrentStockAttribute()
    {
        $stockIn = $this->ledgers()->sum('qty_in');
        $stockOut = $this->ledgers()->sum('qty_out');

        return (float) ($this->opening_stock ?? 0)
            + (float) $stockIn
            - (float) $stockOut;
    }

    public function getStockBalanceAttribute()
    {
        return $this->current_stock;
    }

    public function getStockInAttribute()
    {
        return (float) $this->ledgers()->sum('qty_in');
    }

    public function getStockOutAttribute()
    {
        return (float) $this->ledgers()->sum('qty_out');
    }

    public function getCostPriceAttribute()
    {
        return $this->avg_cost;
    }

    public function getUnitAttribute()
    {
        return $this->product_size;
    }

    public function getFullNameAttribute()
    {
        return $this->product_name . ' (' . ($this->product_size ?? '-') . ')';
    }

    public function getStatusLabelAttribute()
    {
        return $this->status ?? 'Active';
    }

    public function getIsActiveAttribute()
    {
        return ($this->status ?? 'Active') === 'Active';
    }

    public function getIsLowStockAttribute()
    {
        return (float) $this->current_stock <= (float) ($this->reorder_level ?? 0);
    }

    public function getIsOutOfStockAttribute()
    {
        return (float) $this->current_stock <= 0;
    }

    public function getFormattedSellingPriceAttribute()
    {
        return number_format((float) ($this->selling_price ?? 0), 2);
    }

    public function getFormattedCostPriceAttribute()
    {
        return number_format((float) ($this->avg_cost ?? 0), 2);
    }

    public function getFormattedStockValueAttribute()
    {
        return number_format($this->stockValue(), 2);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function totalReceived()
    {
        return (float) $this->ledgers()->sum('qty_in');
    }

    public function totalIssued()
    {
        return (float) $this->ledgers()->sum('qty_out');
    }

    public function stockValue()
    {
        return (float) $this->current_stock * (float) ($this->avg_cost ?? 0);
    }

    public function grossProfit()
    {
        return (float) ($this->selling_price ?? 0) - (float) ($this->avg_cost ?? 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeNotDeleted($query)
    {
        return $query->where('status', '!=', 'Deleted');
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->when($companyId, function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        });
    }

    public function scopeForUnit($query, $unitId)
    {
        return $query->when($unitId, function ($q) use ($unitId) {
            $q->where('comp_unit_id', $unitId);
        });
    }

    public function scopeForWorkPoint($query, $workPointId)
    {
        return $query->when($workPointId, function ($q) use ($workPointId) {
            $q->where('work_point_id', $workPointId);
        });
    }
}