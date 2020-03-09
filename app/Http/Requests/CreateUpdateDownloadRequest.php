<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CreateUpdateDownloadRequest extends Request
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



        switch($this->method()) {
            case 'POST': {
                return [
                    'title_en' => 'required|string',
                    'title_el' => 'required|string',
                    'description_el' => 'required|string',
                    'description_en' => 'required|string',
                    'file'=>'required|max:39000'
                ];
            }
            case 'PATCH': {
                return [
                    'edit_title_en' => 'required|string',
                    'edit_title_el' => 'required|string',
                    'edit_description_el' => 'required|string',
                    'edit_description_en' => 'required|string',
                    'edit_file'=>'sometimes|required|max:39000'
                ];
            }


        }
    }
}
