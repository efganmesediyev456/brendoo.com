<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemStatus extends Model
{
    public $timestamps = false;
    protected $fillable = ['order_item_id', 'status', 'status_id'];

    public function orderItem() : BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function status(): BelongsTo{
        return $this->belongsTo(Status::class);
    }
}
