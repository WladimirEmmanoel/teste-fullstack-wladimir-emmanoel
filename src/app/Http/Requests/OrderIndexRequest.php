<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'affiliate_id' => ['nullable', 'integer', 'exists:affiliates,id'],

            'status' => ['nullable', 'integer'],

            'date_from' => ['nullable', 'date'],

            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],

            'min_value' => ['nullable', 'numeric', 'min:0'],

            'max_value' => ['nullable', 'numeric', 'gte:min_value'],

            'sort_by' => [
                'nullable',
                Rule::in([
                    'id',
                    'affiliate_id',
                    'total_value',
                    'status',
                    'created_at',
                ]),
            ],

            'sort_dir' => [
                'nullable',
                Rule::in(['asc', 'desc']),
            ],
        ];
    }
    
    /**
     * Retorna os filtros validados com os valores padrão.
     */
    public function filters(): array
    {
        return array_merge([
            'sort_by' => 'created_at',
            'sort_dir' => 'desc',
        ], $this->validated());
    }
}
