<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TagStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            'name' => ['required', 'string', 'max:50', 'unique:tags,name'],
            'name_zh' => ['required', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:30'],
            'emotion_type' => ['nullable', 'string', 'max:20'],
            'color' => ['nullable', 'string', 'max:7'],
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
            'name' => '標籤英文名稱',
            'name_zh' => '標籤中文名稱',
            'category' => '分類',
            'emotion_type' => '情緒類型',
            'color' => '顏色',
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
            'name.required' => '標籤英文名稱為必填項目',
            'name.max' => '標籤英文名稱最多 50 個字元',
            'name.unique' => '此標籤英文名稱已存在',
            'name_zh.required' => '標籤中文名稱為必填項目',
            'name_zh.max' => '標籤中文名稱最多 50 個字元',
            'category.max' => '分類最多 30 個字元',
            'emotion_type.max' => '情緒類型最多 20 個字元',
            'color.max' => '顏色最多 7 個字元',
        ];
    }
}
