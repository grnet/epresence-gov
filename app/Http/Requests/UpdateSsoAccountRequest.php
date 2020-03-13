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
            'telephone' => 'required_unless:role,EndUser',
            'thumbnail' => 'image|max:300',
            'institution_id'=>'required',
            'department_id' => 'required',
            'new_department' => 'required_if:department_id,other',
        ];
    }


    public function messages()
    {
        return [
            'telephone.required_unless' => trans('requests.phoneRequired'),
            'thumbnail.image' => trans('requests.photoFileType').': jpeg, png, bmp, gif, svg',
            'thumbnail.max' => trans('requests.maxPhotoSize'),
            'institution_id.required' => trans('requests.institutionRequired'),
            'department_id.required' => trans('requests.departmentRequired'),
            'new_department.required_if' => trans('requests.newDepartmentRequired'),
        ];
    }
}
