<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    use HasFactory;
    protected $table = 'marketing_campaigns';
    protected $fillable = [
        'name',
        'objective',
        'start_date',
        'end_date',
        'user_id',
        'company_id',
        'work_point_id',
        'status',
        'budget',
        'actual_cost',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }
    public function workpoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }
    public function responses()
    {
        return $this->hasMany(CampaignResponse::class, 'marketing_campaign_id');
    }
}