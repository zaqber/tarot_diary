<?php

namespace App\Repositories;

use App\Models\TarotCard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TarotCardRepository implements TarotCardRepositoryInterface
{
    /**
     * 取得所有塔羅牌
     *
     * @param int $perPage 每頁筆數
     * @param array $filters 篩選條件
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = TarotCard::query();

        // 篩選卡片類型
        if (isset($filters['card_type'])) {
            $query->where('card_type', $filters['card_type']);
        }

        // 篩選花色
        if (isset($filters['suit_id'])) {
            $query->where('suit_id', $filters['suit_id']);
        }


        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_zh', 'like', "%{$search}%");
            });
        }

        return $query->with('suit')
                    ->orderBy('card_type')
                    ->orderBy('number')
                    ->paginate($perPage);
    }

    /**
     * 根據 ID 取得單張塔羅牌
     *
     * @param int $id
     * @return \App\Models\TarotCard|null
     */
    public function findById(int $id)
    {
        return TarotCard::with(['suit', 'tags'])->find($id);
    }
}
