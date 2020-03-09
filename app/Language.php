<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Log;
use Storage;
use DB;
use Carbon\Carbon;
use App\LanguageLine;

class Language extends Model
{
    protected $fillable = [
        'language_id', 'active', 'primary', 'code','name'
    ];



    public function scopeActive($query){

        return $query->where("active",true);
    }


    public function import($method = "create_missing"){

        $now = Carbon::now();

        $primary_selected_language = Language::where("primary",true)->first();

        $language = $this;
        //Change language to active since its found in our language files

        $language->update(["active" => true]);
        $is_primary = $language->primary;
        $language_id = $language->id;

        $files = Storage::disk('language_files')->files($language->code);

        foreach ($files as $file) {

            $just_file = explode('/', $file)[1];

            //Find and save original file

            $contents = include resource_path('lang/' . $file);

            $file_name = explode(".", $just_file)[0];

            foreach ($contents as $key => $outer_value) {

                //Second level array

                if (is_array($outer_value)) {
                    foreach ($outer_value as $inner_key => $inner_value) {

                        if (is_array($inner_value)) {

                            foreach ($inner_value as $in_inner_key => $in_inner_value) {

                                $has_tags = false;

                                if ($in_inner_value !== strip_tags($in_inner_value))
                                    $has_tags = true;

                                //Third level array - max level

                                $final_inner_key = $key . '.' . $inner_key . '.' . $in_inner_key;

                                //Try to get original line by key group and language

                                $original_line_exists = LanguageLine::where('group', $file_name)->where('key', $final_inner_key)->where('language_id', $primary_selected_language->id)->first();

                                if ($method == "overwrite") {

                                    //If is primary language and overwrite just insert it

                                    if ($is_primary) {
                                        DB::table('language_lines')->insert(['group' => $file_name, 'language_id' => $language_id, 'key' => $final_inner_key, 'text' => $in_inner_value, 'has_tags' => $has_tags, 'created_at' => $now, 'updated_at' => $now]);
                                    } else {

                                        //Insert if line exists in original file

                                        if (isset($original_line_exists))
                                            DB::table('language_lines')->insert(['group' => $file_name, 'language_id' => $language_id, 'key' => $final_inner_key, 'text' => $in_inner_value, 'original_id' => $original_line_exists->id, 'has_tags' => $has_tags, 'created_at' => $now, 'updated_at' => $now]);

                                    }
                                } else {

                                    $isset = DB::table('language_lines')->where('group', $file_name)->where('language_id', $language_id)->where('key', $final_inner_key)->first();

                                    if (!isset($isset->id)) {

                                        //Insert if line exists in original file

                                        if ($is_primary) {
                                            DB::table('language_lines')->insert(['group' => $file_name, 'language_id' => $language_id, 'key' => $final_inner_key, 'text' => $in_inner_value,'has_tags' => $has_tags, 'created_at' => $now, 'updated_at' => $now]);

                                        } else {

                                            if (isset($original_line_exists))
                                                DB::table('language_lines')->insert(['group' => $file_name, 'language_id' => $language_id, 'key' => $final_inner_key, 'text' => $in_inner_value, 'original_id' => $original_line_exists->id, 'has_tags' => $has_tags, 'created_at' => $now, 'updated_at' => $now]);

                                        }
                                    }
                                }
                            }
                        } else {

                            $has_tags = false;

                            if ($inner_value !== strip_tags($inner_value))
                                $has_tags = true;


                            $final_key = $key . '.' . $inner_key;

                            //Try to get original line by key group and language

                            $original_line_exists = LanguageLine::where('group', $file_name)->where('key', $final_key)->where('language_id', $primary_selected_language->id)->first();

                            if ($method == "overwrite") {

                                if ($is_primary) {
                                    DB::table('language_lines')->insert(['group' => $file_name, 'language_id' => $language_id, 'key' => $final_key, 'text' => $inner_value,'has_tags' => $has_tags, 'created_at' => $now, 'updated_at' => $now]);
                                } else {

                                    //Insert if line exists in original file
                                    if (isset($original_line_exists))
                                        DB::table('language_lines')->insert(['group' => $file_name, 'language_id' => $language_id, 'key' => $final_key, 'text' => $inner_value, 'original_id' => $original_line_exists->id, 'has_tags' => $has_tags, 'created_at' => $now, 'updated_at' => $now]);

                                }


                            } else {
                                $isset = DB::table('language_lines')->where('group', $file_name)->where('language_id', $language_id)->where('key', $final_key)->first();

                                if (!isset($isset->id)) {

                                    if ($is_primary) {
                                        DB::table('language_lines')->insert(['group' => $file_name, 'language_id' => $language_id, 'key' => $final_key, 'text' => $inner_value,'has_tags' => $has_tags, 'created_at' => $now, 'updated_at' => $now]);

                                    } else {

                                        if (isset($original_line_exists))
                                            DB::table('language_lines')->insert(['group' => $file_name, 'language_id' => $language_id, 'key' => $final_key, 'text' => $inner_value, 'original_id' => $original_line_exists->id, 'has_tags' => $has_tags, 'created_at' => $now, 'updated_at' => $now]);

                                    }
                                }
                            }
                        }
                    }
                } else {

                    $has_tags = false;

                    if ($outer_value !== strip_tags($outer_value))
                        $has_tags = true;

                    $original_line_exists = LanguageLine::where('group', $file_name)->where('key', $key)->where('language_id', $primary_selected_language->id)->first();

                    if ($method == "overwrite") {

                        if ($is_primary) {
                            DB::table('language_lines')->insert(['group' => $file_name, 'language_id' => $language_id, 'key' => $key, 'text' => $outer_value, 'has_tags' => $has_tags, 'created_at' => $now, 'updated_at' => $now]);
                        } else {

                            if (isset($original_line_exists))
                                DB::table('language_lines')->insert(['group' => $file_name, 'language_id' => $language_id, 'key' => $key, 'text' => $outer_value, 'original_id' => $original_line_exists->id, 'has_tags' => $has_tags, 'created_at' => $now, 'updated_at' => $now]);

                        }

                    } else {

                        $isset = DB::table('language_lines')->where('group', $file_name)->where('language_id', $language_id)->where('key', $key)->first();

                        if (!isset($isset->id)) {

                            if ($is_primary) {
                                DB::table('language_lines')->insert(['group' => $file_name, 'language_id' => $language_id, 'key' => $key, 'text' => $outer_value, 'has_tags' => $has_tags, 'created_at' => $now, 'updated_at' => $now]);
                            } else {

                                if (isset($original_line_exists))
                                    DB::table('language_lines')->insert(['group' => $file_name, 'language_id' => $language_id, 'key' => $key, 'text' => $outer_value, 'original_id' => $original_line_exists->id, 'has_tags' => $has_tags, 'created_at' => $now, 'updated_at' => $now]);

                            }
                        }
                    }
                }

            }
        }

    }

}




