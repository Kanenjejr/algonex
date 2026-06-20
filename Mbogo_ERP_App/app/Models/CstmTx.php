<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CstmTx extends Model
{
    use HasFactory;
    protected $table = 'cstm_txes';
    protected $fillable = [
        'cstm_id','tx_date','type','amount','balance_after','reference','notes',
        'user_id','company_id','work_point_id'
    ];
    protected $dates = ['tx_date'];
    public function customer() { return $this->belongsTo(CstmSply::class, 'cstm_id'); }
    public function user() { return $this->belongsTo(User::class, 'user_id'); }
}