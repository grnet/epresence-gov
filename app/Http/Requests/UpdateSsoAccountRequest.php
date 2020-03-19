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
            'thumbnail' => 'image|max:300'
        ];
    }


    public function messages()
    {
        return [
            'telephone.required_unless' => trans('requests.phoneRequired'),
            'thumbnail.image' => trans('requests.photoFileType').': jpeg, png, bmp, gif, svg',
            'thumbnail.max' => trans('requests.maxPhotoSize')
        ];
    }
}
