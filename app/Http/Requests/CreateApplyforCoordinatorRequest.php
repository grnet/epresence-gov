<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\User;


class CreateApplyforCoordinatorRequest extends Request
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
                    'ip' => 'sometimes|required',
                    'extension_num' => 'sometimes|required',
                    'lastname' => 'required_with:application',
                    'firstname' => 'required_with:application',
                    'email' => 'required|email|unique:users,email',
                    'state' => 'required',
                    'telephone' => 'sometimes|required_with:application|numeric',
                    'thumbnail' => 'image|max:300',
                    'institution_id' => 'sometimes|required_unless:role,EndUser',
                    'new_department' => 'required_if:department_id,other',
                    'new_institution' => 'required_if:institution_id,other',
                    'accept_terms' => 'sometimes|required|accepted',
                    'comment' => 'sometimes|required',
                    'role' => 'sometimes|required',
                    'my_name'   => 'honeypot',
                    'my_time'   => 'required|honeytime:5'
                ];
            }
            case 'PATCH':
            {
                return [
                    'ip' => 'sometimes|required',
                    'extension_num' => 'sometimes|required',
                    'lastname' => 'sometimes|required',
                    'firstname' => 'sometimes|required',
                    'email' => 'sometimes|required|email|unique:users,email,'.$this->id,
                    'telephone' => 'required_unless:role,EndUser|numeric',
                    'thumbnail' => 'image|max:300',
                    'current_password' => 'sometimes|required_with:password',
                    'password' => 'sometimes|confirmed|required_with:password_confirmation',
                    'password_confirmation' => 'sometimes|required_with:password',
                    'institution_id' => 'required',
                ];
            }
            default:break;
        }
    }

    public function messages()
    {
        return [
            'extension_num.required' => trans('requests.extensionRequired'),
            'ip.required' => trans('requests.ipRequired'),
            'lastname.required_with' => trans('requests.lastnameRequired'),
            'lastname.required' => trans('requests.lastnameRequired'),
            'firstname.required_with' => trans('requests.firstnameRequired'),
            'firstname.required' => trans('requests.firstnameRequired'),
            'email.required' => trans('requests.emailRequired'),
            'email.unique' => trans('requests.emailNotUniqueChangeRole').' <a href="/contact" target="_blank">'.trans('requests.here').'</a>.',
            'email.email' => trans('requests.emailInvalid'),
            'state.required' => trans('requests.localSelectRequired'),
            'telephone.required_with' => trans('requests.phoneRequired'),
            'telephone.required_unless' => trans('requests.phoneRequired'),
            'telephone.numeric' => trans('requests.numberOnlyPhone'),
            'thumbnail.image' => trans('requests.photoFileType').': jpeg, png, bmp, gif, svg',
            'thumbnail.max' => trans('requests.maxPhotoSize'),
            'current_password.required_with:password' => trans('requests.currentPassRequired'),
            'password.required_with:password_confirmation' => trans('requests.newPassRequired'),
            'password_confirmation.required_with:password' => trans('requests.confirmPassRequired'),
            'institution_id.required_unless' => trans('requests.institutionRequired'),
            'institution_id.required' => trans('requests.institutionRequired'),
            'department_id.required_if' => trans('requests.departmentRequired'),
            'department_id.required_without' => trans('requests.departmentRequired'),
            'new_department.required_if' => trans('requests.newDepartmentRequired'),
            'new_institution.required_if' => trans('requests.newInstitutionRequired'),
            'accept_terms.accepted' => trans('requests.acceptTerms'),
            'comment.required' => trans('requests.descriptionRequired'),
            'application.required' => trans('requests.applicationRequired'),
            'role.required' => trans('requests.roleRequired'),
            'my_time.honeytime' => trans('requests.honeytime'),
            'my_time.required' => trans('requests.honeytime'),
            'my_name.honeypot' => trans('requests.honeytime')
        ];
    }

}
