<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Log;

class UpdateLocalAccountRequest extends Request
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
            'lastname' => 'required',
            'firstname' => 'required',
            'telephone' => 'required_unless:role,EndUser',
            'thumbnail' => 'image|max:300',
            'current_password' => 'sometimes|required_with:password',
            'password' => 'sometimes|confirmed|required_with:password_confirmation',
            'password_confirmation' => 'sometimes|required_with:password',
            'institution_id' => 'required_if:role,EndUser',
            'new_institution' => 'required_if:institution_id,other',
            'department_id' => 'required_if:role,EndUser',
            'new_department' => 'required_if:department_id,other',

        ];
    }


    public function messages()
    {


        return [

            'lastname.required' => trans('requests.lastnameRequired'),
            'firstname.required' => trans('requests.firstnameRequired'),

            'telephone.required_unless' => trans('requests.phoneRequired'),

            'thumbnail.image' => trans('requests.photoFileType').': jpeg, png, bmp, gif, svg',
            'thumbnail.max' => trans('requests.maxPhotoSize'),

            'current_password.required_with:password' => trans('requests.currentPassRequired'),
            'password.required_with:password_confirmation' => trans('requests.newPassRequired'),
            'password_confirmation.required_with:password' => trans('requests.confirmPassRequired'),


            'institution_id.required_if' => trans('requests.institutionRequired'),
            'department_id.required_if' => trans('requests.departmentRequired'),


            'new_institution.required_if' => trans('requests.newInstitutionRequired'),
            'new_department.required_if' => trans('requests.newDepartmentRequired'),

        ];
    }
}
