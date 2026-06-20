<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;
    protected $table = 'quotes';
    protected $fillable = [
        'quote_number',
        'quote_date',
        'expiry_date',
        'user_id',
        'company_id',
        'work_point_id',
        'cstm_order_id',
        'cstm_id',
        'sub_total',
        'tax',
        'discount',
        'total',
        'status',
    ];
    public function items()
    {
        return $this->hasMany(QuoteItem::class, 'quote_id');
    }
    public function customer()
    {
        return $this->belongsTo(CstmSply::class, 'cstm_id');
    }
    public function company()
    {
        return $this->belongsTo(CompanySite::class, 'company_id');
    }
    public function workpoint()
    {
        return $this->belongsTo(WorkPoint::class, 'work_point_id');
    }
}
