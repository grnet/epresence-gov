<?php

namespace App\Http\Controllers;

use App\Document;
use App\Download;
use App\Faq;
use App\Video;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Log;
use Auth;
use Storage;

class SupportPageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($type = 'faq')
    {
        $data["total_videos"] = Video::count();
        $data["total_documents"] = Document::count();
        $data["total_downloads"] = Download::count();
        $data["total_faqs"] = Faq::active()->count();
        switch($type){
            case "videos":
                $data["videos"] = Video::orderBy('order','asc')->get();
                break;
            case "downloads":
                $data["downloads"] = Download::orderBy('order','asc')->get();
                break;
            case "documents":
                $data["documents"] = Document::orderBy('order','asc')->get();
                break;
            case "faq":
                $data["faqs"] = Faq::active()->orderBy('order','asc')->get();
                break;
            case "teamviewer":
                //  $data = [];
                break;
        }
        $view = 'support.' . $type;
        return view($view,$data);
    }




    public function store_download(Requests\CreateUpdateDownloadRequest $request)
    {
        if ($request->file('file')->isValid()) {


           $input = $request->all();

           $new_download = new Download;

           $file_response = $new_download->uploadFile($input['file']);

            if($file_response !== 'file_already_exists'){


                //Check again if there is a row with same file_path

                $current_download = Download::where('file_path',$file_response)->first();

                if($current_download)
                    return back()->withErrors(['file_upload'=>'Υπάρχει ήδη ενα αρχείο με αυτό το όνομα'])->withInput();

                $new_download->title_el = $input['title_el'];
                $new_download->title_en = $input['title_en'];
                $new_download->description_el = $input['description_el'];
                $new_download->description_en = $input['description_en'];
                $new_download->file_path = $file_response;

                $new_download->save();

            }else{
                return back()->withErrors(['file_upload'=>'Υπάρχει ήδη ενα αρχείο με αυτό το όνομα'])->withInput();
            }
         } else {
            return back()->withErrors(['file_upload'=>'Σφάλμα κατα το ανέβασμα του αρχείου'])->withInput();
        }

        return back()->with('message','Το αρχείο ανέβηκε επιτυχώς!');
    }


    public function delete_download(Request $request){


        $input = $request->all();

      if(Auth::check() && Auth::user()->hasRole('SuperAdmin')){

          $download_id = $input['download_id'];

          $download = Download::find($download_id);

          if($download) {

              if(Storage::disk('public')->has($download->file_path))
                  Storage::disk('public')->delete($download->file_path);


              $download->delete();
          }

          $response['status'] = 'success';
          $response['message'] = 'Download deleted successfully';


      }else{
         $response['status'] = 'error';
         $response['message'] = 'You do not have the rights to do that';
      }

      return response()->json($response);
    }

    public function update_download(Requests\CreateUpdateDownloadRequest  $request){

        $input = $request->all();

        if(Auth::check() && Auth::user()->hasRole('SuperAdmin')){

            $download_id = $input['download_id'];
            $download = Download::find($download_id);

            if($download){

                $download->title_el = $input['edit_title_el'];
                $download->title_en = $input['edit_title_en'];
                $download->description_el = $input['edit_description_el'];
                $download->description_en = $input['edit_description_en'];

                if($request->hasFile('edit_file') && $request->file('edit_file')->isValid()){


                    if(Storage::disk('public')->has($download->file_path))
                        Storage::disk('public')->delete($download->file_path);

                    $file_store_reponse = $download->uploadFile($input['edit_file']);

                    if($file_store_reponse !== 'file_already_exists')
                    $download->file_path = $file_store_reponse;
                    else
                    return back()->withErrors(['edit_file_upload'=>'Υπάρχει ήδη ενα αρχείο με αυτό το όνομα'])->withInput();

                }

                $download->update();

                return back()->with('message','Το αρχείο ενημερώθηκε επιτυχώς!');
            }
        }else{
            abort(404);
        }
    }


    public function get_download_details_ajax(Request $request){


        $input = $request->all();

        if(Auth::check() && Auth::user()->hasRole('SuperAdmin')){

            $download_id = $input['download_id'];

            $download = Download::find($download_id);

            if($download) {

                $response['status'] = 'success';
                $response['message'] = 'Download found!';
                $response['data'] = $download;
            }
          }else{
            $response['status'] = 'error';
            $response['message'] = 'You do not have the rights to do that';
        }

        return response()->json($response);
    }

    public function show_cookies_page(){

        return view('cookies');
    }



}
