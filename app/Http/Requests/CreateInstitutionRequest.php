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
                    'contact_email' => 'nullable|email',
                    'shibboleth_domain' => 'nullable|unique:institutions,shibboleth_domain'
                ];
            }
            case 'PATCH': {
                return [
                    'title' => 'required|not_in:Άλλο|unique:institutions,title,'.$this->id,
                    'contact_email' => 'nullable|email',
                    'shibboleth_domain' => 'nullable|unique:institutions,shibboleth_domain,'.$this->id
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
            'slug.not_in' => trans('requests.idNotOther'),
			'url.url' => trans('requests.webAddressFormat'),
			'contact_email.email' => trans('requests.emailFormat'),
			'shibboleth_domain.unique' => trans('requests.shibbolethDomainUnique')
        ];
    }
}
