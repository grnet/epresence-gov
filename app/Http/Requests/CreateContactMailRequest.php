<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CreateContactMailRequest extends Request
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
        return [
            'fullname' => 'sometimes|required',
            'email' => 'sometimes|required|email',
            'text' => 'sometimes|required',
            'title' => 'sometimes|required',
            'my_name'   => 'honeypot',
            'my_time'   => 'required|honeytime:5'
        ];
    }

    public function messages()
    {


        return [
            'fullname.required' => trans('requests.fullnameRequired'),
            'email.required' => trans('requests.emailRequired'),
            'email.email' => trans('requests.emailInvalid'),
            'text.required' => trans('requests.messageRequired'),
            'title.required' => trans('requests.titleRequired'),
            'my_time.honeytime' => trans('requests.honeytime'),
            'my_time.required' => trans('requests.honeytime'),
            'my_name.honeypot' => trans('requests.honeytime')
        ];
    }
}
