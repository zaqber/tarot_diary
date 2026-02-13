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
        // 取得當前用戶 ID（如果有的話）
        $userId = $request->user()?->id;

        // 組織標籤資料
        $tagsData = $this->whenLoaded('tags', function () use ($userId) {
            $defaultTags = [];
            $customTags = [];

            foreach ($this->tags as $tag) {
                $tagData = [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'name_zh' => $tag->name_zh,
                    'position' => $tag->pivot->position,
                    'color' => $tag->color ?? null,
                ];

                // 區分系統預設和用戶自訂
                if ($tag->pivot->is_default && $tag->pivot->user_id === null) {
                    $defaultTags[] = $tagData;
                } elseif ($userId && $tag->pivot->user_id === $userId) {
                    $customTags[] = $tagData;
                }
            }

            // active: 如果有自訂標籤則使用自訂，否則使用系統預設
            $activeTags = !empty($customTags) ? $customTags : $defaultTags;

            return [
                'active' => $activeTags,  // 當前使用的標籤（優先自訂，否則預設）
                'default' => $defaultTags, // 系統預設標籤（永遠存在）
                'custom' => $customTags,  // 用戶自訂標籤（如果有）
            ];
        }, function () use ($userId) {
            // 如果沒有 eager load，則手動查詢
            $defaultTags = $this->defaultTags()->get()->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'name_zh' => $tag->name_zh,
                    'position' => $tag->pivot->position,
                    'color' => $tag->color ?? null,
                ];
            })->toArray();

            $customTags = [];
            if ($userId) {
                $customTags = $this->customTags($userId)->get()->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'name_zh' => $tag->name_zh,
                        'position' => $tag->pivot->position,
                        'color' => $tag->color ?? null,
                    ];
                })->toArray();
            }

            // active: 如果有自訂標籤則使用自訂，否則使用系統預設
            $activeTags = !empty($customTags) ? $customTags : $defaultTags;

            return [
                'active' => $activeTags,  // 當前使用的標籤（優先自訂，否則預設）
                'default' => $defaultTags, // 系統預設標籤（永遠存在）
                'custom' => $customTags,  // 用戶自訂標籤（如果有）
            ];
        });

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
            'tags' => $tagsData,
            'images' => [
                'upright' => $this->image_url,
                'reversed' => $this->image_url_reversed,
            ],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
