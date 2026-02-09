<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TarotCardSetTagsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // 需要用戶認證，但這裡先設為 true，實際使用時可以加上認證中間件
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
            'tags' => ['required', 'array'],
            'tags.*.name' => ['sometimes', 'string', 'max:50'],
            'tags.*.name_zh' => ['required', 'string', 'max:50'],
            'tags.*.position' => ['required', 'string', 'in:upright,reversed,both'],
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
            'tags' => '標籤',
            'tags.*.name' => '標籤英文名稱',
            'tags.*.name_zh' => '標籤中文名稱',
            'tags.*.position' => '位置',
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
            'tags.required' => '標籤為必填項目',
            'tags.array' => '標籤必須是陣列格式',
            'tags.*.name.max' => '標籤英文名稱最多 50 個字元',
            'tags.*.name_zh.required' => '標籤中文名稱為必填項目',
            'tags.*.name_zh.max' => '標籤中文名稱最多 50 個字元',
            'tags.*.position.required' => '位置為必填項目',
            'tags.*.position.in' => '位置必須是 upright（正位）、reversed（逆位）或 both（雙向）其中之一',
        ];
    }
}

