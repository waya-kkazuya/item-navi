<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateItemRequest extends FormRequest
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
        // Vue側の命名規則であることに注意
        return [
            // 'image1' => ['nullable'], // 正方形画像 画像名の命名規則にしたがって制限をかける、何文字以内
            'imageFile' => ['nullable', 'image', 'mimes:jpeg,png,jpg'],
            'name' => ['required', 'min:1' ,'max:20'],
            'categoryId' => ['required', 'exists:categories,id'],
            'stock' => ['required', 'integer', 'min:0', 'max:200'],
            'unitId' => ['required', 'exists:units,id'] ,
            'minimumStock' =>  ['integer', 'min:0', 'max:50'],
            'notification' => ['required', 'boolean'],
            'usageStatusId' => ['required', 'exists:usage_statuses,id'],
            'endUser' => ['nullable','max:10'],
            'locationOfUseId' => ['required', 'exists:locations,id'],
            'storageLocationId' => ['required', 'exists:locations,id'],
            'acquisitionMethodId' => ['required', 'exists:acquisition_methods,id'],
            'acquisitionSource' => ['min:1', 'max:20'],
            'price' => ['required', 'integer', 'min:0', 'max:1000000'],
            'dateOfAcquisition' => ['required', 'date'],
            'manufacturer' => ['nullable', 'max:20'],
            'productNumber' => ['nullable', 'max:30'],
            'remarks' => ['nullable', 'max:500'],
            
            // inspectionScheduleのバリデーション
            'inspectionSchedule' => [
                'date',
                'nullable',
                function ($attribute, $value, $fail) {
                    $dateOfAcquisition = Carbon::parse(request()->input('dateOfAcquisition'));
                    $inspectionSchedule = Carbon::parse($value);
                    if ($inspectionSchedule->lt($dateOfAcquisition)) {
                        $fail('点検予定日は取得年月日以降の日付を入力してください');
                    }                    
                    if ($inspectionSchedule->gt($dateOfAcquisition->addYears(3))) {
                        $fail('点検予定日は取得年月日から3年以内の日付を入力してください');
                    }
                },
            ],
            
            // disposalScheduleのバリデーション
            'disposalSchedule' => [
                'date',
                'nullable',
                function ($attribute, $value, $fail) {
                    $dateOfAcquisition = Carbon::parse(request()->input('dateOfAcquisition'));
                    $disposalSchedule = Carbon::parse($value);
                    if ($disposalSchedule->lt($dateOfAcquisition)) {
                        $fail('廃棄予定日は取得年月日以降の日付を入力してください');
                    }                    
                    if ($disposalSchedule->gt($dateOfAcquisition->addYears(3))) {
                        $fail('廃棄予定日は取得年月日から3年以内の日付を入力してください');
                    }
                },
            ],

            // 編集理由のバリデーション
            'editReasonId' => ['required', 'exists:edit_reasons,id'],
            'editReasonText' => ['nullable', 'max:200'],
        ];
    }
}
