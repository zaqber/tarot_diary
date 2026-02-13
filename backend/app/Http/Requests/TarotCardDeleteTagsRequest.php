<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TarotCardDeleteTagsRequest extends FormRequest
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
            'tags' => ['required', 'array'],
            'tags.*.tag_id' => ['sometimes', 'integer'],
            'tags.*.name' => ['sometimes', 'string', 'max:50'],
            'tags.*.name_zh' => ['sometimes', 'string', 'max:50'],
            'tags.*.position' => ['sometimes', 'string', 'in:upright,reversed,both'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($this->input('tags', []) as $index => $tag) {
                // 必須提供 tag_id 或 (name 或 name_zh)
                if (empty($tag['tag_id']) && empty($tag['name']) && empty($tag['name_zh'])) {
                    $validator->errors()->add(
                        "tags.{$index}",
                        '必須提供 tag_id 或 tag 名稱（name 或 name_zh）'
                    );
                }
            }
        });
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
            'tags.*.tag_id' => '標籤 ID',
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
            'tags.*.tag_id.integer' => '標籤 ID 必須是整數',
            'tags.*.name.max' => '標籤英文名稱最多 50 個字元',
            'tags.*.name_zh.max' => '標籤中文名稱最多 50 個字元',
            'tags.*.position.in' => '位置必須是 upright（正位）、reversed（逆位）或 both（雙向）其中之一（不提供則刪除所有位置）',
        ];
    }
}

