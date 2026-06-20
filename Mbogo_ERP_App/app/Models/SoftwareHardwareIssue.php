<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\ItMaintenance;

class SoftwareHardwareIssue extends Model
{
    use SoftDeletes;

    protected $table = 'software_hardware_issues';

    protected $primaryKey = 'issue_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'device_name',
        'issue_type',
        'category',
        'problem_description',
        'priority_level',
        'date_reported',
        'assigned_to',
        'issue_status',
        'resolution_details',
        'resolved_date',
    ];

    protected $casts = [
        'date_reported' => 'datetime',
        'resolved_date' => 'datetime',
    ];

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function maintenances()
    {
        return $this->hasMany(ItMaintenance::class, 'issue_id', 'issue_id');
    }
}