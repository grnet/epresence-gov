<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateDownloadRequest extends FormRequest
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
            "order"=>"required",
           "title_el"=>"required",
            "title_en"=>"required",
            "description_el"=>"required",
            "description_en"=>"required",
            "file_path"=>"required",
        ];
    }
}
