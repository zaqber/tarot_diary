<?php

namespace App\Services;

use App\Repositories\TagRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Tag;

class TagService
{
    protected TagRepositoryInterface $repository;

    public function __construct(TagRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取得標籤列表
     *
     * @param array $params 查詢參數
     * @return Collection
     */
    public function getTags(array $params = []): Collection
    {
        $filters = [
            'search' => $params['search'] ?? null,
            'category' => $params['category'] ?? null,
            'emotion_type' => $params['emotion_type'] ?? null,
        ];

        $filters = array_filter($filters, fn($value) => !is_null($value));

        return $this->repository->getAll($filters);
    }

    /**
     * 新增標籤
     *
     * @param array $data
     * @return Tag
     */
    public function createTag(array $data): Tag
    {
        return $this->repository->create($data);
    }

    /**
     * 修改標籤
     *
     * @param int $id
     * @param array $data
     * @return Tag|null
     */
    public function updateTag(int $id, array $data): ?Tag
    {
        return $this->repository->update($id, $data);
    }

    /**
     * 刪除標籤
     *
     * @param int $id
     * @return bool
     * @throws \RuntimeException 當標籤被牌使用時
     */
    public function deleteTag(int $id): bool
    {
        if ($this->repository->hasCards($id)) {
            throw new \RuntimeException('此標籤已被塔羅牌使用，無法刪除');
        }

        return $this->repository->delete($id);
    }
}
