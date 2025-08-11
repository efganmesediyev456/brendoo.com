<?php

namespace App\Models;

use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'title',
        'description',
        'short_description',
        'blog_id',
        'locale',
        'img_alt',
        'img_title',
        'slug',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'short_title'
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }


    
    protected static function boot()
{
    parent::boot();

    static::created(function ($model) {
        $product = method_exists($model, 'product') ? $model->product()->first() : null;

        activity('ProductTranslation')
            ->performedOn($product ?? $model)
            ->causedBy(auth()->user())
            ->withProperties([
                'attributes' => $model->getAttributes(),
                'locale' => $model->locale ?? null,
                'model_type' => class_basename($model),
                'model_id' => $model->id,
                'product_id' => $product?->id
            ])
            ->event('created')
            ->log('created');
    });

    static::updated(function ($model) {
        $changes = $model->getDirty();
        if (empty($changes)) return;

        $product = method_exists($model, 'product') ? $model->product()->first() : null;

        activity('ProductTranslation')
            ->performedOn($product ?? $model)
            ->causedBy(auth()->user())
            ->withProperties([
                'attributes' => $model->getAttributes(),
                'old' => $model->getOriginal(),
                'changes' => $changes,
                'locale' => $model->locale ?? null,
                'model_type' => class_basename($model),
                'model_id' => $model->id,
                'product_id' => $product?->id
            ])
            ->event('updated')
            ->log('updated');
    });
    }


}
