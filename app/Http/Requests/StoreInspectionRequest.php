<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInspectionRequest extends FormRequest
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
            'inspectionSchedule' => ['nullable','date'],
            'inspectionDate' => ['required', 'date'], // 消耗品管理で保存する時に使う
            // 'status' => ['required', 'boolean'],
            'inspectionPerson' => ['required', 'max:10'] ,
            'details' => ['required', 'max:200'] ,
        ];
    }
}
