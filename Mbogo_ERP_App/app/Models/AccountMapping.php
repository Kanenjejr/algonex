<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountMapping extends Model
{
    protected $fillable = [
        'module',
        'type',
        'account_code',
        'account_name'
    ];
}