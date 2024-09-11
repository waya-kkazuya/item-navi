<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\StoreInspectionRequest;
use App\Http\Requests\StoreDisposalRequest;
use Illuminate\Session\Store;

class CombinedRequest extends FormRequest
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
        return array_merge(
            (new StoreItemRequest())->rules(),
            (new StoreInspectionRequest())->rules(),
            (new StoreDisposalRequest())->rules()
        );
    }
}
