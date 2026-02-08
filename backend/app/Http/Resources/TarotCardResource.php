<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TarotCardResource extends JsonResource
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
            'name_zh' => $this->name_zh,
            'card_type' => $this->card_type,
            'number' => $this->number,
            'suit' => $this->whenLoaded('suit', function () {
                return [
                    'id' => $this->suit->id ?? null,
                    'name' => $this->suit->name ?? null,
                    'name_zh' => $this->suit->name_zh ?? null,
                    'element' => $this->suit->element ?? null,
                ];
            }),
            'suit_id' => $this->suit_id,
            'official_meaning' => [
                'upright' => $this->official_meaning_upright,
                'reversed' => $this->official_meaning_reversed,
            ],
            'self_definition' => [
                'upright' => $this->self_definition_upright,
                'reversed' => $this->self_definition_reversed,
            ],
            'keywords' => [
                'upright' => $this->keywords_upright ? explode(',', $this->keywords_upright) : [],
                'reversed' => $this->keywords_reversed ? explode(',', $this->keywords_reversed) : [],
            ],
            'images' => [
                'upright' => $this->image_url,
                'reversed' => $this->image_url_reversed,
            ],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
