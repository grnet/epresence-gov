<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Log;

class CreateDocumentRequest extends FormRequest
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

        Log::info($this->request->all());

        return [
            "title_en"=>"required",
            "title_el"=>"required",
            "en_file"=>"required_without_all:el_file,en_file_url,el_file_url|file|max:4000",
            "el_file"=>"required_without_all:en_file,en_file_url,el_file_url|file|max:4000",
            "en_file_url"=>"required_without_all:el_file,en_file,el_file_url",
            "el_file_url"=>"required_without_all:el_file,en_file,en_file_url",
        ];
    }
}
