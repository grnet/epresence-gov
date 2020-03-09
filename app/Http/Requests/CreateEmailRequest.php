<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CreateEmailRequest extends Request
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
            ];
    }
}
