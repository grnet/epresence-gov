<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\User;


class CreateUserRequest extends Request
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
				'email' => 'required|email|unique:users,email|unique:users_extra_emails,email,0,confirmed',
				'state' => 'required',
				'telephone' => 'sometimes|required_with:application,AdminSubmitBtnNew',
				'thumbnail' => 'image|max:300',
				'institution_id' => 'sometimes|required_unless:role,EndUser',
				'new_department' => 'required_if:department_id,other',
				'new_institution' => 'required_if:institution_id,other',
				'accept_terms' => 'sometimes|required|accepted',
				'comment' => 'sometimes|required',
				'role' => 'sometimes|required',
            ];
        }
        case 'PATCH':
        {
            return [
				'ip' => 'sometimes|required',
                'extension_num' => 'sometimes|required',
                'lastname' => 'sometimes|required_if:confirmed,1',
				'firstname' => 'sometimes|required_if:confirmed,1',
				'email' => 'sometimes|required|email|unique:users,email,'.$this->id.'|unique:users_extra_emails,email,0,confirmed',
                'extra_sso_email_1' => 'unique:users,email,0,confirmed|unique:users_extra_emails,email,0,confirmed',
                'extra_sso_email_2' => 'unique:users,email,0,confirmed|unique:users_extra_emails,email,0,confirmed',
				'telephone' => 'required_unless:role,EndUser',
				'thumbnail' => 'image|max:300',
				'current_password' => 'sometimes|required_with:password',
				'password' => 'sometimes|confirmed|required_with:password_confirmation',
				'password_confirmation' => 'sometimes|required_with:password',
				'institution_id' => 'required_if:confirmed,1',
				'application' => 'required_with:application_current',
			];
        }
        default:break;
		}
    }

    public function messages()
    {


        return [
            'lastname.required_if' => trans('requests.lastnameRequired'),
            'firstname.required_if' => trans('requests.firstnameRequired'),
            'email.unique' => trans('requests.emailNotUnique'),
            'institution_id.required_if' => trans('requests.departmentRequired'),
            'application.required_with' => trans('requests.applicationRequired'),
            'extension_num.required' => trans('requests.extensionRequired'),
            'ip.required' => trans('requests.ipRequired'),
            'lastname.required_with' => trans('requests.lastnameRequired'),
            'lastname.required' => trans('requests.lastnameRequired'),
            'firstname.required_with' => trans('requests.firstnameRequired'),
            'firstname.required' => trans('requests.firstnameRequired'),
            'email.required' => trans('requests.emailRequired'),
            'email.email' => trans('requests.emailInvalid'),
            'extra_sso_email_1.unique' => trans('requests.extraEmail1NotUnique'),
            'extra_sso_email_2.unique' => trans('requests.extraEmail2NotUnique'),
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

        ];
    }

}
