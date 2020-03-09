<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Auth;
use Log;

class ActivateSsoAccountRequest extends Request
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

    public function rules()
    {
        return [
            'lastname' => 'required',
            'firstname' => 'required',
            'thumbnail' => 'image|max:300',
            'department_id' => 'required',
            'new_department' => 'required_if:department_id,other',
            'email' => 'required|email|unique:users,email,'.Auth::user()->id.',id,state,sso,confirmed,1',
            'extra_sso_email_1' => 'unique:users,email,'.Auth::user()->id.',id,state,sso,confirmed,1',
            'extra_sso_email_2' => 'unique:users,email,'.Auth::user()->id.',id,state,sso,confirmed,1',
            'accept_terms_input' => 'required',
            'privacy_policy_input' => 'required',

        ];
    }


    public function messages()
    {

        return [
            'lastname.required' => trans('requests.lastnameRequired'),
            'firstname.required' => trans('requests.firstnameRequired'),
            'thumbnail.image' => trans('requests.photoFileType').': jpeg, png, bmp, gif, svg',
            'thumbnail.max' => trans('requests.maxPhotoSize'),
            'department_id.required' => trans('requests.departmentRequired'),
            'new_department.required_if' => trans('requests.newDepartmentRequired'),
            'email.unique' => trans('requests.emailNotUnique'),
            'email.email' => trans('requests.emailRequired'),
            'email.required' => trans('requests.emailRequired'),
            'extra_sso_email_1.unique' => trans('requests.extraEmail1NotUnique'),
            'extra_sso_email_2.unique' => trans('requests.extraEmail2NotUnique'),
            'accept_terms_input.required' => trans('site.mustAcceptTermsActivate'),
            'privacy_policy_input.required' => trans('site.acceptPrivacyPolicyActivate'),
        ];
    }
}
