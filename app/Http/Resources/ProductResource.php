<?php

namespace App\Http\Resources;

use App\Models\Filter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $base_url = asset('storage');
        $totalComments = $this?->comments?->count();

        $totalComments = optional($this->comments)->count() ?? 0;

        $ratingsCount = optional($this->comments)
            ?->groupBy('star')
            ?->map(fn($comments) => $comments->count())
            ?->all() ?? [];

        $ratingPercentages = [];

        for ($i = 5; $i >= 1; $i--) {
            $count = $ratingsCount[$i] ?? 0;
            $percentage = $totalComments > 0 ? round(($count / $totalComments) * 100) : 0;
            $ratingPercentages[$i] = "{$percentage}% ({$count})";
        }

        $filters = Filter::whereIn('id', $this->options->pluck('pivot.filter_id'))->get();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'short_title' => $this->short_title,
            'is_return' => (bool)$this->is_return,
            'is_new' => $this->is_new,
            'is_stock' => $this->is_stock,
            'is_season' => $this->is_season,
            'code' => $this->code,
            'meta_title' => $this->meta_title ?? '',
            'meta_description' => $this->meta_description ?? '',
            'meta_keywords' => $this->meta_keywords ?? '',
            'img_alt' => $this->img_alt ?? '',
            'img_title' => $this->img_title ?? '',
            'description' => $this->description ?? '',
            'slug' => [
                'en' => optional($this->translate('en'))->slug,
                'ru' => optional($this->translate('ru'))->slug,
            ],
            'price' => $this->price,
            'discount' => $this->discount,
            'discounted_price' => $this->discounted_price,
            'comments_count' => $totalComments,
            'avg_star' => (round($this?->comments?->avg('star'), 1)) ?: 5,
            'unit' => $this->unit,
            'brand' => new BrandResource($this->brand),
            'image' => $this->image,
            'size_image' => "{$base_url}/{$this->size_image}",
            'sliders' => SliderResource::collection($this->sliders),
            'comments' => CommentResource::collection($this?->comments),
            'rating_summary' => $ratingPercentages,
            'filters' => $this->options
                ->groupBy('pivot.filter_id')
                ->map(function ($options, $filterId) use ($filters) {
                    $filter = $filters->firstWhere('id', $filterId);
                    return [
                        'filter_id' => $filterId,
                        'filter_name' => $filter->title ?? '',
                        'options' => $options->map(function ($option) {
                            return [
                                'option_id' => $option->id,
                                'name' => $option->title,
//                                'is_default' => $option->pivot->is_default,
                                'is_stock' => $option->pivot->is_stock,
                                'color_code' => $option->color_code,
                            ];
                        })->toArray(),
                    ];
                })->values()->toArray(),
        ];
    }

}
