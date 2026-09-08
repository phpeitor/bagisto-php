<?php

namespace Webkul\Shop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Webkul\Core\Rules\PhoneNumber;
use Webkul\Customer\Facades\Captcha;

class ComplaintBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return Captcha::getValidations([
            'document_type' => 'required|in:dni,ce,pasaporte',
            'document_number' => 'required|string|max:20',
            'last_name' => 'required|string|max:191',
            'first_name' => 'required|string|max:191',
            'address' => 'required|string|max:191',
            'phone' => new PhoneNumber,
            'email' => 'required|email',
            'good_type' => 'required|in:producto,servicio',
            'good_description' => 'required|string|max:191',
            'claimed_amount' => 'nullable|numeric|min:0',
            'type' => 'required|in:reclamo,queja',
            'detail' => 'required|string',
            'request' => 'required|string',
            'accept_privacy' => 'accepted',
        ]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return Captcha::getValidationMessages();
    }
}
