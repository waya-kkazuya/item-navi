<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequestRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'min:3' ,'max:20'],
            'categoryId' => ['required', 'exists:categories,id'],
            'locationOfUseId' => ['required', 'exists:locations,id'],
            'requestor' => ['required', 'min:1' ,'max:20'],
            'remarksFromRequestor' => ['required', 'max:500'],
            'requestStatusId' => ['nullable'], // admin・staffが変更する、enum型か、テーブルを追加するか
            'manufacturer' => ['nullable', 'max:20'],
            'reference' => ['nullable', 'max:20'],
            'price' => ['nullable', 'integer', 'max:1000000'],
        ];
    }
}
