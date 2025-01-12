<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class WithdrawRequest extends FormRequest
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
            'personName' =>'required|string',
            'availableWithdrawal' => 'nullable|numeric|regex:/^\d{1,5}(\.\d{1,2})?$/',
            'withdrawnAmount' => 'required|numeric|regex:/^\d{1,5}(\.\d{1,2})?$/',
            'remainingAmount' => 'nullable|numeric|regex:/^\d{1,5}(\.\d{1,2})?$/',
            'description' =>'required|string',
            'creationDate'=> 'nullable|date_format:Y-m-d H:i:s',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ]));
    }
}
