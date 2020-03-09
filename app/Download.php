<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

use Backpack\CRUD\CrudTrait;

class Download extends Model
{

    use CrudTrait;


    protected $fillable = ['title_el', 'title_en', 'description_el', 'description_en','file_path','order'];


    public function uploadFile($file){

        //create a file path
        $path = 'support_files/uploads/';

        //get the file name
        $file_name = $file->getClientOriginalName();


//        if(!Storage::disk('public')->has($path.$file_name)) {

            //save the file to your path
            $file->move($path, $file_name); //( the file path , Name of the file)
//        }else{
//            return 'file_already_exists';
//        }


        return $path.$file_name;
    }

    public function setFilePathAttribute($value)
    {
        if(!is_string($value) && !empty($value))
        $response =  $this->uploadFile($value);
        else
        $response = $value;

        if($response === "file_already_exists"){
            $response = null;
        }

        $this->attributes['file_path'] = $response;
    }


    public function get_download_link() {

        return '<a href="'.$this->file_path.'" target="_blank">Download</a>';
    }







}
