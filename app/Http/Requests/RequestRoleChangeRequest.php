<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Support\Facades\Log;

class RequestRoleChangeRequest extends Request
{

    public function authorize()
    {
        Log::info(json_encode(request()->all()));
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
                    'application_telephone' => 'required',
                    'accept_terms' => 'required',
                    'application_comment' => 'required',
                    'application_role' => 'required',
                    'institution_id'=>'required',
                    'department_id'=>'required_if:application_role,DepartmentAdministrator',
                    'new_department'=>'required_if:department_id,other'
                ];
    }

    public function messages()
    {
        return [
            'application_telephone.required' => trans('requests.phoneRequired'),
            'accept_terms.required' => trans('requests.acceptTerms'),
            'application_comment.required' => trans('requests.descriptionRequired'),
            'application_role.required' => trans('requests.roleRequired'),
            'institution_id.required' => trans('requests.institutionRequired'),
            'department_id.required' => trans('requests.departmentRequired'),
            'department_id.required_if' => trans('requests.departmentRequired'),
            'new_department.required_if' => trans('requests.newDepartmentRequired'),
        ];
    }
}
