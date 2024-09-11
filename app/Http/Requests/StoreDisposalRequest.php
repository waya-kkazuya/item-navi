<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDisposalRequest extends FormRequest
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
            // 'item_id' => ['required', 'exists:items,id'],
            'disposal_scheduled_date' => ['nullable', 'date'],
            'disposal_date' => ['required', 'date'], //現実の物理的な廃棄の日付
            'disposal_person' => ['required', 'max:10'] ,
            'details' => ['required', 'max:200'] ,
        ];
    }
}
