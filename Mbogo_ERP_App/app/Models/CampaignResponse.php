<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignResponse extends Model
{
    protected $fillable = [
        'campaign_id','user_id','customer_id','contact_id',
        'response_type','notes','response_date','status',
        'company_id','work_point_id'
    ];

    public function campaign(){ return $this->belongsTo(Campaign::class); }
    public function customer(){ return $this->belongsTo(Customer::class); }
    public function contact(){ return $this->belongsTo(Contact::class); }
    public function user(){ return $this->belongsTo(User::class); }
}