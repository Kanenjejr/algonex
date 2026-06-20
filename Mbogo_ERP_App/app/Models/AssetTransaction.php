<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetTransaction extends Model
{
    use HasFactory;
    protected $table='asset_transactions';
    protected $fillable = [
        'asset_name','asset_tag','asset_category_id','purchase_date','purchase_cost','depreciation_rate',
        'useful_life_years','accumulated_depreciation','transaction_type','transaction_date',
        'disposal_value','revalue_amount','description','status',
        'user_id','company_id','work_point_id'
    ];
    protected $casts = [
        'purchase_date' => 'date',
        'transaction_date' => 'date',
    ];
    public function category() { return $this->belongsTo(AssetCategory::class, 'asset_category_id'); }
    public function user() { return $this->belongsTo(User::class); }
    public function companySite() { return $this->belongsTo(CompanySite::class, 'company_id'); }
    public function workPoint() { return $this->belongsTo(WorkPoint::class, 'work_point_id'); }
    /**
     * Calculate straight-line depreciation for a full year based on depreciation_rate %
     * Example: purchase_cost=1000, rate=25 => yearly = 250
     */
    public function yearlyDepreciation()
    {
        return round(($this->purchase_cost * ($this->depreciation_rate / 100)), 2);
    }
    /**
     * Calculate prorated depreciation between two dates (inclusive start, exclusive end).
     * If only $year provided, it calculates depreciation for that calendar year.
     * Behavior:
     * - If asset purchased during the year, depreciation is prorated on months held.
     * - If asset disposed in the year, depreciation prorated up to disposal date.
     *
     * Example usage:
     *   $asset->proratedDepreciationForYear(2025);
     */
    public function proratedDepreciationForYear(int $year)
    {
        $rate = $this->depreciation_rate;
        $annual = ($this->purchase_cost * ($rate / 100.0));
        // determine holding period within the requested year
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($year, 12, 31)->endOfDay();
        // define acquisition date
        $acq = $this->purchase_date ? Carbon::parse($this->purchase_date)->startOfDay() : null;
        // disposal date if exists (transaction_date when status = Disposed or transaction_type=disposal)
        $disposedOn = null;
        if ($this->status === 'Disposed' && $this->transaction_date) {
            $disposedOn = Carbon::parse($this->transaction_date)->startOfDay();
        }
        // if no acquisition date, zero
        if (! $acq) return 0.0;
        // compute start & end for counting
        $start = $acq->greaterThan($yearStart) ? $acq : $yearStart;
        $end = $disposedOn ? ($disposedOn->lessThan($yearEnd) ? $disposedOn : $yearEnd) : $yearEnd;
        if ($start > $end) return 0.0;
        // We'll prorate by exact days (more precise) or by months? Your example used months — we will use months with partial month prorating.
        // Count months held including partial month fraction
        $daysHeld = $end->diffInDays($start) + 1; // include both dates
        $daysInYear = $yearStart->isLeapYear() ? 366 : 365;
        $prorated = round($annual * ($daysHeld / $daysInYear), 2);
        return $prorated;
    }
    /**
     * Helper: compute months fraction between two dates (useful if you want month-based proration)
     * Not used by default, but provided for auditing.
     */
    public function monthsProrateBetween(Carbon $start, Carbon $end)
    {
        $months = ($end->diffInMonths($start));
        // compute leftover days ratio
        $startPlusMonths = $start->copy()->addMonths($months);
        $extraDays = $end->diffInDays($startPlusMonths);
        $daysInMonth = $startPlusMonths->daysInMonth;
        return $months + ($extraDays / $daysInMonth);
    }
}
