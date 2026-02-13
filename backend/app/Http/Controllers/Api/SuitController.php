<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SuitController extends Controller
{
    /**
     * 取得所有花色列表（手動抽牌時先選 suit，沒有 suit 則為大牌）
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $suits = DB::table('suits')
            ->orderBy('id')
            ->get(['id', 'name', 'name_zh', 'element']);
        return $this->successResponse($suits->toArray(), '取得花色列表成功');
    }
}
