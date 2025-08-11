<?php

namespace App\Models;

use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{

    use HasFactory,LogsActivityTrait;
    protected $guarded = [];

    protected static function booted(): void
    {
        static::created(function ($orderItem) {
            // OrderItemStatus::create([
            //     'order_item_id' => $orderItem->id,
            //     'status' => 'ordered',
            // ]);
        });
    }

    public $casts = [
        'created_at'=>'datetime:d.m.Y'
    ];

    public function options() : HasMany
    {
        return $this->hasMany(OrderItemOption::class);
    }

    public function product() : BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function packages() : BelongsToMany
    {
        return $this->belongsToMany(Package::class);
    }

    public function returnProduct() : HasOne
    {
        return $this->hasOne(ReturnProduct::class, 'order_item_id');
    }

    public function customer() : BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function statuses() : HasMany
    {
        return $this->hasMany(OrderItemStatus::class)->whereHas('status', function($query){
            return $query->where('type',1);
        });;
    }

    public function lastStatus() : HasOne
    {
       return $this->hasOne(OrderItemStatus::class)->whereHas('status', function($query){
            return $query->where('type',1);
        })->latestOfMany();
    }


    public function adminStatus(){
       return $this->belongsTo(Status::class,'admin_status');
    }
    
    public function collection(){
        return $this->belongsTo(Collection::class,'collection_id');
    }


}
