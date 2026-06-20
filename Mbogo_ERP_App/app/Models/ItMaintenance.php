<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SoftwareHardwareIssue;

class ItMaintenance extends Model
{
    use SoftDeletes;

    protected $table = 'it_maintenance';

    protected $primaryKey = 'maintenance_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'issue_id',
        'asset_id',
        'maintenance_type',
        'description',
        'technician_name',
        'maintenance_date',
        'status',
        'cost',
        'remarks',
    ];

    protected $casts = [
        'maintenance_date' => 'datetime',
        'cost' => 'decimal:2',
    ];

    public function issue()
    {
        return $this->belongsTo(SoftwareHardwareIssue::class, 'issue_id', 'issue_id');
    }
}