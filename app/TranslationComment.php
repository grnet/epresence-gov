<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TranslationComment extends Model
{
    public function line()
    {
        return $this->BelongsTo('App\LanguageLine','language_line_id');
    }
}
