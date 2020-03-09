<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CreateDepartmentRequest extends Request
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
            'title' => 'required|not_in:Άλλο',
            'slug' => 'not_in:other',
            'institution_id' => 'required'
        ];
    }
    public function messages()
    {
        return [
            'title.required' => trans('requests.descriptionRequired'),
			'title.not_in' => trans('requests.descriptionNotOther'),
            'slug.not_in' => trans('requests.idNotOther'),
            'institution_id.required' => trans('requests.institutionRequired')
        ];
    }


}
