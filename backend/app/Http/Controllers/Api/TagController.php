<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TagStoreRequest;
use App\Http\Requests\TagUpdateRequest;
use App\Http\Resources\TagResource;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Dedoc\Scramble\Attributes\QueryParameter;
use Dedoc\Scramble\Attributes\PathParameter;

class TagController extends Controller
{
    protected TagService $service;

    public function __construct(TagService $service)
    {
        $this->service = $service;
    }

    /**
     * 取得標籤列表
     *
     * 支援搜尋、分類、情緒類型篩選。
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[QueryParameter('search', 'string', '搜尋關鍵字（名稱）', required: false)]
    #[QueryParameter('category', 'string', '分類篩選', required: false)]
    #[QueryParameter('emotion_type', 'string', '情緒類型篩選', required: false)]
    public function index(Request $request): JsonResponse
    {
        $params = [
            'search' => $request->input('search'),
            'category' => $request->input('category'),
            'emotion_type' => $request->input('emotion_type'),
        ];

        $tags = $this->service->getTags($params);

        return $this->successResponse(
            TagResource::collection($tags),
            '取得標籤列表成功'
        );
    }

    /**
     * 新增標籤
     *
     * @param TagStoreRequest $request
     * @return JsonResponse
     */
    public function store(TagStoreRequest $request): JsonResponse
    {
        try {
            $tag = $this->service->createTag($request->validated());

            return $this->successResponse(
                new TagResource($tag),
                '標籤新增成功',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('新增標籤時發生錯誤：' . $e->getMessage(), 500);
        }
    }

    /**
     * 修改標籤
     *
     * @param TagUpdateRequest $request
     * @param int $id 標籤 ID
     * @return JsonResponse
     */
    #[PathParameter('id', 'integer', '標籤 ID', example: 1)]
    public function update(TagUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $tag = $this->service->updateTag($id, $request->validated());

            if (!$tag) {
                return $this->errorResponse('找不到此標籤', 404);
            }

            return $this->successResponse(
                new TagResource($tag),
                '標籤修改成功'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('修改標籤時發生錯誤：' . $e->getMessage(), 500);
        }
    }

    /**
     * 刪除標籤
     *
     * 若標籤已被塔羅牌使用，則禁止刪除。
     *
     * @param int $id 標籤 ID
     * @return JsonResponse
     */
    #[PathParameter('id', 'integer', '標籤 ID', example: 1)]
    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->service->deleteTag($id);

            if (!$result) {
                return $this->errorResponse('找不到此標籤', 404);
            }

            return $this->successResponse(null, '標籤刪除成功');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 409);
        } catch (\Exception $e) {
            return $this->errorResponse('刪除標籤時發生錯誤：' . $e->getMessage(), 500);
        }
    }
}
