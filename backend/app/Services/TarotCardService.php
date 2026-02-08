<?php

namespace App\Services;

use App\Repositories\TarotCardRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TarotCardService
{
    protected TarotCardRepositoryInterface $repository;

    public function __construct(TarotCardRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取得塔羅牌列表
     *
     * @param array $params 查詢參數
     * @return LengthAwarePaginator
     */
    public function getCards(array $params = []): LengthAwarePaginator
    {
        $perPage = $params['per_page'] ?? 15;
        $filters = [
            'card_type' => $params['card_type'] ?? null,
            'suit_id' => $params['suit_id'] ?? null,
            'search' => $params['search'] ?? null,
        ];

        // 移除空值
        $filters = array_filter($filters, fn($value) => !is_null($value));

        return $this->repository->getAllPaginated($perPage, $filters);
    }

    /**
     * 取得單張塔羅牌詳細資訊
     *
     * @param int $id
     * @return \App\Models\TarotCard|null
     */
    public function getCardById(int $id)
    {
        return $this->repository->findById($id);
    }

}


