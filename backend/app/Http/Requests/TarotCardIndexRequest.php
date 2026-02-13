<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TarotCardIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // 允許所有人訪問
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => '參數驗證失敗',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'card_type' => ['sometimes', 'string', 'in:major,minor'],
            'suit_id' => ['sometimes', 'integer', 'min:1', 'max:4'],
            'search' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'per_page' => '每頁筆數',
            'card_type' => '卡片類型',
            'suit_id' => '花色 ID',
            'search' => '搜尋關鍵字',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'per_page.integer' => '每頁筆數必須是整數',
            'per_page.min' => '每頁筆數最少為 1',
            'per_page.max' => '每頁筆數最多為 100',
            'card_type.in' => '卡片類型必須是 major（大牌）或 minor（小牌）',
            'suit_id.integer' => '花色 ID 必須是整數',
            'suit_id.min' => '花色 ID 最小為 1',
            'suit_id.max' => '花色 ID 最大為 4（1: 權杖, 2: 聖杯, 3: 寶劍, 4: 錢幣）',
            'search.max' => '搜尋關鍵字最多 255 個字元',
        ];
    }
}
