<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CopyResource extends JsonResource
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
            'barcode' => $this->barcode,
            'condition' => $this->condition,
            'location' => $this->location,
            'is_available' => $this->is_available,
            'notes' => $this->notes,
            'status' => $this->is_available ? 'available' : 'borrowed',
            'current_loan' => new LoanResource($this->whenLoaded('currentLoan')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
