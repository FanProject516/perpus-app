<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
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
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'publisher' => $this->publisher,
            'year' => $this->year,
            'summary' => $this->summary,
            'cover_url' => $this->cover_path ? asset('storage/' . $this->cover_path) : null,
            'total_copies' => $this->total_copies,
            'available_copies' => $this->available_copies,
            'price' => $this->price ? (float) $this->price : null,
            'language' => $this->language,
            'pages' => $this->pages,
            'condition' => $this->condition,
            'location' => $this->location,
            'is_available' => $this->is_available,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'copies' => CopyResource::collection($this->whenLoaded('copies')),
            'availability_status' => $this->is_available && $this->available_copies > 0 ? 'available' : 'unavailable',
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
