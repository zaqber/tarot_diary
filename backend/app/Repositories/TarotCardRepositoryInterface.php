<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TarotCardRepositoryInterface
{
    /**
     * 取得所有塔羅牌
     *
     * @param int $perPage 每頁筆數
     * @param array $filters 篩選條件
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * 根據 ID 取得單張塔羅牌
     *
     * @param int $id
     * @return \App\Models\TarotCard|null
     */
    public function findById(int $id);
}


