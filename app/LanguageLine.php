<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Log;

class LanguageLine extends Model
{

    protected $fillable = [
        'group', 'language', 'key', 'text', 'dirty','original_id'
    ];

    public function original_line()
    {
        return $this->belongsTo('App\LanguageLine','original_id');
    }


    public function note()
    {
        return $this->HasOne('App\TranslationComment','language_line_id');
    }


    public function translations()
    {
        return $this->hasMany('App\LanguageLine','original_id');
    }


    public function language()
    {
        return $this->belongsTo('App\Language','language_id');
    }


    public function get_other_translations_languages(){

        $data['available_translations'] = array();

        if ($this->language->primary) {
            $data['available_translations'] = $this->translations()->with('language')->get();

        } else {

            $data['available_translations'][] = $this->original_line;

            $rest_translated_languages = $this->original_line->translations()->where('id','!=',$this->id)->get();

            foreach($rest_translated_languages as $other_language_lines){
                $data['available_translations'][] = $other_language_lines;
            }

         }

        return $data['available_translations'];
      }

}
