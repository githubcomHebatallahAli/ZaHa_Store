<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
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
            'creationDate'=> 'nullable|date_format:Y-m-d H:i:s',
            'product_id' => 'required|exists:products,id',
            'customerName' => 'required|string',
            'sellerName' => 'required|string',
            'invoiceProductNum'=> 'nullable|integer',
            'invoicePrice' => 'required|numeric|regex:/^\d{1,5}(\.\d{1,2})?$/',
            'discount' => 'nullable|numeric|regex:/^\d{1,5}(\.\d{1,2})?$/',
            'invoiceAfterDiscount' => 'nullable|numeric|regex:/^\d{1,5}(\.\d{1,2})?$/',

        ];
    }
}
