<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Language;
use App\LanguageLine;
use App\Settings;
use App\TranslationComment;
use Illuminate\Http\Request;
use Log;
use Storage;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;
use Auth;

class LanguageFileController extends Controller
{


    public function export(Request $request)
    {

        $locked = Settings::where('category','admin')->where('title','locked_language_files')->first()->option;

//        $language_codes = Storage::disk('language_files')->directories();

        //Get all languages

        if(!$locked) {

            $language_list = DB::table('language_lines')->select('language_id')->groupBy('language_id')->get();

            foreach ($language_list as $language_id) {

                //Get all en groups/filenames

                $lan = Language::find($language_id->language_id);
                $lan_groups = DB::table('language_lines')->select('group')->where('language_id', $lan->id)->groupBy('group')->get();

                //Create Files

                foreach ($lan_groups as $group) {

                    $group_rows = DB::table('language_lines')->select('key', 'text')->where('group', $group->group)->where('language_id', $lan->id)->get();

                    $multi_level_arrays = array();

                    $contents = '<?php' . PHP_EOL . PHP_EOL;
                    $contents .= 'return [' . PHP_EOL . PHP_EOL;

                    foreach ($group_rows as $row) {
                        $key_exploded = explode('.', $row->key);

                        if (count($key_exploded) == 1) {
                            $contents .= format_text_for_language_file($key_exploded[0], $row->text, 4);
                        } else {

                            if (count($key_exploded) == 2) {
                                $multi_level_arrays[$key_exploded[0]][$key_exploded[1]] = $row->text;
                            } elseif (count($key_exploded) == 3) {
                                $multi_level_arrays[$key_exploded[0]][$key_exploded[1]][$key_exploded[2]] = $row->text;
                            }
                        }
                    }

                    //Attach multilevel arrays to output

                    foreach ($multi_level_arrays as $key => $first_level) {
                        if (is_array($first_level)) {
                            $contents .= str_repeat(" ", 4) . '"' . $key . '"=>[' . PHP_EOL;

                            foreach ($first_level as $inner_key => $second_level) {

                                if (!is_array($second_level)) {

                                    $contents .= format_text_for_language_file($inner_key, $second_level, 8);


                                } else {

                                    $contents .= str_repeat(" ", 6) . '"' . $inner_key . '"=>[' . PHP_EOL;

                                    foreach ($second_level as $in_inner_key => $sl) {

                                        //Third level array - max level
                                        $contents .= format_text_for_language_file($in_inner_key, $sl, 8);

                                    }

                                    $contents .= str_repeat(" ", 6) . '],' . PHP_EOL;
                                }
                            }
                            $contents .= str_repeat(" ", 4) . '],' . PHP_EOL;
                        }
                    }
                    $contents .= PHP_EOL . "];";

                    Storage::disk('language_files')->put($lan->code . '/' . $group->group . '.php', $contents);
                }
            }

            Settings::where('title', 'exported_language_files')->where('category', 'admin')->update(["option" => "1"]);

            $response['form'] = "export_form";
            $response['status'] = "success";
            $response['message'] = "Export successful!";

        }else{
            $response['form'] = "export_form";
            $response['status'] = "error";
            $response['message'] = "You can't export files right now!";
       }

        return back()->with($response);
    }

    public function index(Request $request)
    {
        $language_list = Language::where("active", true)->get();

        $data['languages'] = $language_list;


        $groups = LanguageLine::select('group')->groupBy('group')->get();
        $data['groups'] = $groups;

        $language_lines = LanguageLine::with('original_line');


        if (!$request->has('same_page')) {

            if ($request->has('term') && !empty(trim($request->term))) {

                $term = $request->term;

                $language_lines = $language_lines->where(function ($query) use ($term) {

                    $query->where('text', 'like', '%' . $term . '%')->orWhere('key', 'like', '%' . $term . '%');

                });

                //Return language lines that have this term in their original line

                if ($request->has('search_in_original')) {
                    $language_lines = $language_lines->orWhere(function ($query) use ($term) {
                        $query->whereHas('original_line', function ($inner_query) use ($term) {
                            $inner_query->where(function ($query) use ($term) {
                                $query->where('text', 'like', '%' . $term . '%')->orWhere('key', 'like', '%' . $term . '%');
                            });
                        });
                    });
                }
            }

                if ($request->has('language_id') && $request->language_id !== "all")
                    $language_lines = $language_lines->where('language_id', $request->language_id);


            if ($request->has('group') && $request->group !== "all") {
                $language_lines = $language_lines->where('group', $request->group);
            }

            if ($request->has('dirty') && $request->dirty !== "all") {
                $language_lines = $language_lines->where('dirty', $request->dirty);
            }

            $language_lines = $language_lines->orderBy('key', 'desc')->paginate(20);

        } else {


            $language_lines = $language_lines->where("id", $request->id)->get();

        }

        if (!empty($request->id)) {

            $data['currently_editing'] = $request->id;
            $editing_line = LanguageLine::find($request->id);
            $data['available_translations'] = $editing_line->get_other_translations_languages();

        } else {

            $data['currently_editing'] = null;
            $data['other_languages_available'] = null;
        }

        //Handle highlighting and truncate

        foreach ($language_lines as $line) {

            if (!$request->has('id') || ($request->has('id') && $request->id != $line->id)) {

                //Strip tags for preview strings

                $line->text = strip_tags($line->text);

                //Truncate preview

                if (mb_strlen($line->text) > 200)
                    $line->text = mb_substr($line->text, 0, 200) . '...';

                //Truncate original

                if (isset($line->original_line)) {

                    $line->original_line->text = strip_tags($line->original_line->text);

                    if (mb_strlen($line->original_line->text) > 200)
                        $line->original_line->text = mb_substr($line->original_line->text, 0, 200) . '...';
                }

                //Highlight search term
                if ($request->has('term') && !empty(trim($request->term)))
                    $line->text = preg_replace("/" . preg_quote($request->term, "/") . "/ui", "<span class='highlighted'>$0</span>", $line->text);

            }
        }


        $data['language_lines'] = $language_lines;


        $language_codes = Storage::disk('language_files')->directories();
        $available_languages = Language::whereIn("code", $language_codes)->get();
        $data['available_languages'] = $available_languages;


        $exported_setting = Settings::where('title','exported_language_files')->where('category','admin')->first();

        if(isset($exported_setting))
            $data['exported'] = $exported_setting->option == "0" ? false : true;
        else
            $data['exported'] = true;


        $data['locked'] = Settings::where('category','admin')->where('title','locked_language_files')->first()->option;

        return view('admin.language_files.index', $data);
    }

    public function update_language_line(Request $request)
    {

        $locked = Settings::where('category','admin')->where('title','locked_language_files')->first()->option;

        if(!$locked) {

            $input = $request->all();
            $line = LanguageLine::find($input['id']);

            if (isset($line)) {

                if ($line->language->primary) {
                    $line->translations()->update(['dirty' => true]);

                    if (!empty($input['note'])) {

                        if (isset($line->note)) {
                            $line->note->text = $input['note'];
                            $line->note->update();
                        } else {
                            $new_comment = new TranslationComment;
                            $new_comment->text = $input['note'];
                            $line->note()->save($new_comment);
                        }
                    }
                }

                $line->dirty = false;

                //Strip tags if they where not any tags in the original line

                if (!$line->has_tags)
                    $line->text = strip_tags($input['value']);
                else
                    $line->text = $input['value'];


                $line->update();


                Settings::where('title', 'exported_language_files')->where('category', 'admin')->update(["option" => "0"]);

                $response['form'] = "manage_lines";
                $response['status'] = "success";
                $response['message'] = "Language line updated!";

            } else {
                $response['form'] = "manage_lines";
                $response['status'] = "error";
                $response['message'] = "Language line not found or permissions error occurred!";


            }
        }else{
            $response['form'] = "manage_lines";
            $response['status'] = "error";
            $response['message'] = "Language line can't be updated right now!";

        }
        return back()->with($response);
    }
}
