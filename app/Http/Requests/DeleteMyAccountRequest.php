<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class DeleteMyAccountRequest extends Request
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
        switch($this->method()){
            case 'POST':{
                return [
                    'delete_account_confirmation_email'=>'required|email'
                ];
            }

        }
    }

    public function messages()
    {
        return [
            'delete_account_confirmation_email.required' => trans('requests.primaryMailRequired'),
            'delete_account_confirmation_email.email' => trans('requests.emailInvalid'),
        ];
    }
}
