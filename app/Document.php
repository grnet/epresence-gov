<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;

class Document extends Model
{

    use CrudTrait;

    protected $fillable = ['title_el', 'title_en', 'en_file', 'el_file', 'en_file_url', 'el_file_url','order'];


    public function uploadFile($file)
    {

        //create a file path
        $path = 'docs/';

        //get the file name
        $file_name = $file->getClientOriginalName();


        $file->move($path, $file_name); //( the file path , Name of the file)


        return $path . $file_name;
    }

    public function setEnFileAttribute($value)
    {

        if (!is_string($value) && !empty($value))
            $response = '/'.$this->uploadFile($value);
        else
            $response = $value;


        $this->attributes['en_file'] = $response;
    }

    public function setElFileAttribute($value)
    {

        if (!is_string($value) && !empty($value))
            $response = '/'.$this->uploadFile($value);
        else
            $response = $value;

        $this->attributes['el_file'] = $response;
    }



    public function has_link_attached(){

        $en_url = $this->get_en_download_url();
        $el_url = $this->get_el_download_url();



        if(empty($en_url) && empty($el_url))
        $valid = false;
        else
        $valid = true;



        return $valid;
    }

    public function get_en_download_link() {

        $link = "no file / url ";
        $url = $this->get_en_download_url();

        if(!empty($url))
            $link = '<a href="'.$url.'" target="_blank">Download</a>';

        return $link;
    }

    public function get_el_download_link() {

        $link = "no file / url ";
        $url = $this->get_el_download_url();

        if(!empty($url))
            $link = '<a href="'.$url.'" target="_blank">Download</a>';

        return $link;
    }

    public function get_en_download_url()
    {

        if (!empty($this->en_file)) {
            $file_url = $this->en_file;
        } else {
            $file_url = $this->en_file_url;
        }

        return $file_url;
    }


    public function get_el_download_url()
    {

        if (!empty($this->el_file)) {
            $file_url = $this->el_file;
        } else {
            $file_url = $this->el_file_url;
        }

        return $file_url;
    }

}
