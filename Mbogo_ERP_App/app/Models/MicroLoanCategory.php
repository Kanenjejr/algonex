<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroLoanCategory extends Model
{
    use HasFactory;

    protected $table = 'micro_loan_categories';

    protected $fillable = [
        'category_name',
        'description',
        'status',
    ];

    public function products()
    {
        return $this->hasMany(MicroLoanProduct::class, 'loan_category_id');
    }

    public function applications()
    {
        return $this->hasMany(MicroLoanApplication::class, 'loan_category_id');
    }
}