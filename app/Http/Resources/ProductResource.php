<?php

namespace App\Http\Resources;

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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'brand' => $this->brand,
            'image_url' => $this->image_url ?? '/images/placeholder.jpg',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'stock' => [
                'id' => $this->stock->id ?? null,
                'quantity' => $this->stock->quantity ?? 0,
                'sale_price' => $this->stock->sale_price ?? 0,
                'purchase_price' => $this->stock->purchase_price ?? 0,
            ],
        ];
    }
}
