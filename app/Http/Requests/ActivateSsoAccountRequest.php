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
            'email' => 'required|email|unique:users,email,'.Auth::user()->id.',id|unique:users_extra_emails,email',
            'accept_terms_input' => 'required',
            'privacy_policy_input' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'email.unique' => trans('requests.emailNotUnique'),
            'email.email' => trans('requests.emailRequired'),
            'email.required' => trans('requests.emailRequired'),
            'accept_terms_input.required' => trans('site.mustAcceptTermsActivate'),
            'privacy_policy_input.required' => trans('site.acceptPrivacyPolicyActivate'),
        ];
    }
}
