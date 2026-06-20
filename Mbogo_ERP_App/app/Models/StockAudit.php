<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAudit extends Model
{
    use HasFactory;

    protected $table = 'stock_audits';

    protected $fillable = [
        'company_id',
        'comp_unit_id',
        'work_point_id',
        'audit_date',
        'audit_type',
        'remarks',
        'created_by',
        'status',
        'approved_by',
        'approved_at',
        'closed_by',
        'closed_at',
    ];

    protected $casts = [
        'audit_date'  => 'date',
        'approved_at' => 'datetime',
        'closed_at'   => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }

    public function unit()
    {
        return $this->belongsTo(Company_unit::class, 'comp_unit_id');
    }
    public function workpoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function items()
    {
        return $this->hasMany(StockAuditItem::class, 'stock_audit_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function totalSystem()
    {
        return (float) $this->items()->sum('system_qty');
    }

    public function totalPhysical()
    {
        return (float) $this->items()->sum('physical_qty');
    }

    public function totalCounted()
    {
        return (float) $this->items()->sum('counted_qty');
    }

    public function totalVariance()
    {
        return (float) $this->items()->sum('variance_qty');
    }

    public function variance()
    {
        return (float) (
            $this->items()
                ->selectRaw('SUM(physical_qty - system_qty) as variance_total')
                ->value('variance_total') ?? 0
        );
    }

    public function refreshItemVariances()
    {
        foreach ($this->items as $item) {
            $item->update([
                'counted_qty'  => (float) $item->physical_qty,
                'variance_qty' => (float) $item->physical_qty - (float) $item->system_qty,
            ]);
        }

        return $this;
    }

    public function isOpen()
    {
        return $this->status === 'Open';
    }

    public function isApproved()
    {
        return $this->status === 'Approved';
    }

    public function isClosed()
    {
        return $this->status === 'Closed';
    }

    public function canBeApproved()
    {
        return $this->status === 'Open';
    }

    public function canBeClosed()
    {
        return $this->status === 'Approved';
    }

    public function canBeEdited()
    {
        return $this->status !== 'Closed';
    }

    public function canBeDeleted()
    {
        return $this->status !== 'Closed';
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case 'Open':
                return 'Open';

            case 'Approved':
                return 'Approved';

            case 'Closed':
                return 'Closed';

            default:
                return 'Unknown';
        }
    }

    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'Open':
                return 'success';

            case 'Approved':
                return 'primary';

            case 'Closed':
                return 'secondary';

            default:
                return 'secondary';
        }
    }

    public function getAuditTypeLabelAttribute()
    {
        switch ($this->audit_type) {
            case 'GeneralSupply':
                return 'General Supply';

            case 'RawMaterial':
                return 'Raw Material';

            case 'Product':
                return 'Product';

            default:
                return 'General Audit';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOpen($query)
    {
        return $query->where('status', 'Open');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'Closed');
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->when($companyId, function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        });
    }

    public function scopeForWorkPoint($query, $workPointId)
    {
        return $query->when($workPointId, function ($q) use ($workPointId) {
            $q->where('work_point_id', $workPointId);
        });
    }

    public function scopeForType($query, $type)
    {
        return $query->when($type, function ($q) use ($type) {
            $q->where('audit_type', $type);
        });
    }
}