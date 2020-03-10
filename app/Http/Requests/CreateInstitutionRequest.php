<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CreateInstitutionRequest extends Request
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

        switch($this->method()) {
            case 'POST': {
                return [
                    'title' => 'required|not_in:Άλλο|unique:institutions,title',
                    'ws_id' => 'nullable|integer',
                    'api_code' => 'nullable|integer'
                ];
            }
            case 'PATCH': {
                return [
                    'title' => 'required|not_in:Άλλο|unique:institutions,title,'.$this->id,
                    'ws_id' => 'nullable|integer',
                    'api_code' => 'nullable|integer'
                ];
            }
        }
    }
    public function messages()
    {
        return [
            'title.unique' => trans('requests.InstitutionTitleUnique'),
            'title.required' => trans('requests.descriptionRequired'),
            'title.not_in' => trans('requests.descriptionNotOther'),
        ];
    }
}
