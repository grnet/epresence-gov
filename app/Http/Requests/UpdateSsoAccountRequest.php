<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class UpdateSsoAccountRequest extends Request
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
            'telephone' => 'required_unless:role,EndUser',
            'thumbnail' => 'image|max:300',
            'department_id' => 'required_unless:role,SuperAdmin',
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

            'department_id.required_unless' => trans('requests.departmentRequired'),
            'new_department.required_if' => trans('requests.newDepartmentRequired'),
        ];
    }
}
