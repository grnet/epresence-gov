<?php

namespace App\Console\Commands;

use App\LanguageLine;
use Illuminate\Console\Command;
use App\Language;
use DB;
use Storage;
use Carbon\Carbon;

class InstallLanguageFileManager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:language_file_manager {primary_language_code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs language file manager';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $now = Carbon::now();

        $input['primary_language'] = $this->argument('primary_language_code');


        $primary_selected_language = Language::where('code', $input['primary_language'])->first();

        $language_codes = Storage::disk('language_files')->directories();

        if (isset($primary_selected_language) && in_array($primary_selected_language->code, $language_codes)) {


            //Set selected language as primary

            if (isset($primary_selected_language)) {
                Language::where("primary", true)->update(["primary" => false]);
                $primary_selected_language->update(["primary" => true]);
            } else {
                $this->error("Primary language selected is not available in languages table!");
            }

            //Clean table and start over if user selected overwrite mode

            LanguageLine::where("id","!=",null)->delete();

            //Primary language needs to be processed first

            $available_languages = Language::whereIn("code", $language_codes)->orderBy('primary', 'desc')->get();

            foreach ($available_languages as $language) {
                $language->import("overwrite");
            }

            //Create missing keys from original language

            $original_language_lines = LanguageLine::where('language_id', $primary_selected_language->id)->get();

            foreach ($original_language_lines as $original_line) {

                $active_languages = Language::where("primary", false)->where("active", true)->get();


                foreach ($active_languages as $active_language) {

                    $isset = LanguageLine::where("group", $original_line->group)->where("key", $original_line->key)->where("language_id", $active_language->id)->first();

                    if (!isset($isset)){
                        DB::table('language_lines')->insert(
                            [
                                'group' => $original_line->group,
                                'language_id' => $active_language->id,
                                'key' => $original_line->key,
                                'original_id'=>$original_line->id,
                                'text' => "",
                                'has_tags' => $original_line->has_tags,
                                'dirty'=>true,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                        }

                }
            }

            $this->info("Language file manager is installed!");

        } else {

            $this->error("Primary language code is invalid");
        }

    }
}
