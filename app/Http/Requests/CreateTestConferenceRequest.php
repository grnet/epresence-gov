<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Conference;
use App\Settings;

class CreateTestConferenceRequest extends Request
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
			switch($this->method())
		{
			case 'GET':
			case 'DELETE':
			{
				return [];
			}
			case 'POST':
			{
				return [
					'title' => 'required|max:500',
                ];
			}
			case 'PUT':
			case 'PATCH':
			{
				return [
					'title' => 'sometimes|required|max:500',
                ];
			}
			default:break;
		}
    }
	
    public function messages()
    {

        return [
            'title.required' => trans('requests.titleRequired'),
			'title.max'=>trans('requests.titleLength'),
			'title.alpha_dash' => trans('requests.titleAlphadash'),
            ];
    }


}
