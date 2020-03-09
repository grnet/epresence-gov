<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Conference;
use App\Settings;

class CreateConferenceRequest extends Request
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
                    'desc' => 'max:30000',
                    'desc_en' => 'max:30000',
					'start_date' => 'required',
					'start_time' => 'required',
					'end_date' => 'required',
					'end_time' => 'required',
                    'apella_id' => 'nullable|integer',
                    'max_duration'=>'nullable|integer'
				];
			}
			case 'PUT':
			case 'PATCH':
			{
				return [
					'title' => 'sometimes|required|max:500',
                    'desc' => 'max:30000',
                    'desc_en' => 'max:30000',
					'start_date' => 'required',
					'start_time' => 'required',
					'end_date' => 'required',
					'end_time' => 'required',
                    'apella_id' => 'nullable|integer',
                    'max_duration'=>'nullable|integer'
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
            'desc.max'=>trans('requests.descriptionLength'),
            'desc_en.max'=>trans('requests.descriptionLength'),
			'title.alpha_dash' => trans('requests.titleAlphadash'),
            'start_date.required' => trans('requests.startDateRequired'),
            'start_date.date' => trans('requests.dateType'),
            'start_time.required' => trans('requests.startTimeRequired'),
            'end_date.required' => trans('requests.endDateRequired'),
            'end_time.required' => trans('requests.endTimeRequired'),
            'apella_id.integer' => trans('requests.apellaInteger')
        ];
    }


}
