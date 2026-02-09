<?php

namespace App\Repositories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TagRepository implements TagRepositoryInterface
{
    /**
     * 取得所有標籤
     *
     * @param array $filters 篩選條件
     * @return Collection
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Tag::query();

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_zh', 'like', "%{$search}%");
            });
        }

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['emotion_type'])) {
            $query->where('emotion_type', $filters['emotion_type']);
        }

        return $query->orderBy('id')->get();
    }

    /**
     * 根據 ID 取得單一標籤
     *
     * @param int $id
     * @return Tag|null
     */
    public function findById(int $id): ?Tag
    {
        return Tag::find($id);
    }

    /**
     * 新增標籤
     *
     * @param array $data
     * @return Tag
     */
    public function create(array $data): Tag
    {
        return Tag::create($data);
    }

    /**
     * 修改標籤
     *
     * @param int $id
     * @param array $data
     * @return Tag|null
     */
    public function update(int $id, array $data): ?Tag
    {
        $tag = Tag::find($id);
        if (!$tag) {
            return null;
        }

        $tag->update($data);
        return $tag;
    }

    /**
     * 刪除標籤
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $tag = Tag::find($id);
        if (!$tag) {
            return false;
        }

        return $tag->delete();
    }

    /**
     * 檢查標籤是否有被牌使用
     *
     * @param int $id
     * @return bool
     */
    public function hasCards(int $id): bool
    {
        return DB::table('card_tags')->where('tag_id', $id)->exists();
    }
}
