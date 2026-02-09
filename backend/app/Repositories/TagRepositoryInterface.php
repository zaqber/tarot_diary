<?php

namespace App\Repositories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

interface TagRepositoryInterface
{
    /**
     * 取得所有標籤
     *
     * @param array $filters 篩選條件
     * @return Collection
     */
    public function getAll(array $filters = []): Collection;

    /**
     * 根據 ID 取得單一標籤
     *
     * @param int $id
     * @return Tag|null
     */
    public function findById(int $id): ?Tag;

    /**
     * 新增標籤
     *
     * @param array $data
     * @return Tag
     */
    public function create(array $data): Tag;

    /**
     * 修改標籤
     *
     * @param int $id
     * @param array $data
     * @return Tag|null
     */
    public function update(int $id, array $data): ?Tag;

    /**
     * 刪除標籤
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * 檢查標籤是否有被牌使用
     *
     * @param int $id
     * @return bool
     */
    public function hasCards(int $id): bool;
}
