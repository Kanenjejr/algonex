<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampNew extends Model
{
    use SoftDeletes;

    protected $table = 'camp_news';

    protected $fillable = [
        'title',
        'content',
        'image',
        'publish_at',
        'expires_at',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'publish_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function scopeVisible($query)
    {
        return $query
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('publish_at')
                  ->orWhere('publish_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            });
    }
}