<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'amount' => (float) $this->amount,
            'category' => $this->category?->name ?? '',
            'category_icon' => $this->category?->icon,
            'date' => $this->date instanceof \Carbon\Carbon
                ? $this->date->format('Y-m-d')
                : (string) $this->date,
            'description' => $this->note ?? '',
        ];
    }
}
