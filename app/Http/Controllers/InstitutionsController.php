<?php

namespace App\Http\Controllers;

use App\Domain;
use App\Institution;
use App\Department;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use Illuminate\View\View;

class InstitutionsController extends Controller
{
	public function __construct()
	{
    $this->middleware('auth', ['except' => ['listDepartments', 'adminDepartmentFromID','listDepartmentsOtherOrg']]);
	}
	
    public function index()
	{
		if (Gate::denies('view_institutions')) {
            abort(403);
        }
		
		// Limit
		$limit = Input::get('limit') ?: 10;
		$institutions_default = Institution::select();
		$institutions_default = Institution::advancedSearch($institutions_default, Input::all());
		$institutions = $institutions_default->paginate($limit);
		return view('institutions.index', compact('institutions'));
	}
	
	public function show($id)
	{
		$institution = Institution::findOrFail($id);
		
		// Session::put('previous_url', URL::previous());
		
		return redirect("/institutions/".$id."/edit");
	}

    /**
     * @param Requests\CreateInstitutionRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
	public function store(Requests\CreateInstitutionRequest $request)
	{
		if (Gate::denies('view_institutions')) {
            abort(403);
        }
		$input = $request->all();
		$input['created_at'] = Carbon::now();
		$input['updated_at'] = Carbon::now();
		if(isset($input['add_new'])){
			$institution = Institution::create($input);
		}
		return redirect('institutions')->with('storesSuccessfully', trans('controllers.newInstitutionSaved'));
	}

    /**
     * @param $id
     * @return Factory|View
     */
	public function edit($id)
	{
		if (Gate::denies('view_institutions')) {
            abort(403);
        }
		
		$institution = Institution::findOrFail($id);
		return view('institutions.edit', compact('institution'));
	}

    /**
     * @param $id
     */
	public function delete($id)
	{
		if (Gate::denies('view_institutions')) {
            abort(403);
        }
		
		$institution = Institution::findOrFail($id);
		if($institution->users()->count() > 0){
			$results = array(
					'status' => 'error',
					'data' => trans('controllers.cannotDeleteInstitution')
				);
		}else{
			$institution->delete();
		
		$results = array(
					'status' => 'success',
					'data' => trans('controllers.institutionDeleted')
				);
		}
		echo json_encode($results);
	}

    /**Updates The institution
     * @param Requests\CreateInstitutionRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
	public function update(Requests\CreateInstitutionRequest $request, $id)
	{
		if (Gate::denies('view_institutions')) {
            abort(403);
        }
		$institution = Institution::findOrFail($id);
		$input = $request->all();
		$input['updated_at'] = Carbon::now();
		$institution->update($input);
		return redirect('institutions')->with('storesSuccessfully', trans('controllers.changesSaved'));;
	}

    /**
     * @param $id
     * @return string
     */
	public function listDepartments($id)
	{
        $institution = Institution::findOrFail($id);
		$departments = $institution->departments()->orderBy('title')->get();
		$html = '<option value=""></option>';
		foreach($departments as $department){
            $html .= '<option value="'.$department->id.'">'.$department->title.'</option>';
		}
		if(!request()->has('include_other') || request()->input('include_other') == 1){
            $html .= '<option value="other">'.trans('controllers.other').'</option>';
        }
		return $html;
	}


    /**
     * @param $id
     * @param null $department_id
     * @return string
     */
    public function listDepartmentsWrealOther($id, $department_id = null)
    {
        $institution = Institution::findOrFail($id);
        $departments = $institution->departments()->orderBy('title')->get();
        $html = '<option value=""></option>';
        foreach($departments as $department){
            $html .= '<option value="'.$department->id.'">'.$department->title.'</option>';
        }
        return $html;
    }


    /**
     * @return string
     */
    public function listDepartmentsOtherOrg()
    {
        $institution = Institution::where("slug","other")->first();
        $departments = $institution->departments()->where('slug', '<>', 'other')->orderBy('title')->get();
        $html = '<option value=""></option>';
        foreach($departments as $department){
            $html .= '<option value="'.$department->id.'">'.$department->title.'</option>';
        }
        $html .= '<option value="other">'.trans('controllers.other').'</option>';
        return $html;
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
	public function loadDepartmentTable($id) {
        $institution = Institution::findOrFail($id);
		$departments = $institution->departments()->where('slug', '<>', 'other')->orderBy('title')->get();
		$json = array();
		$json['aaData'] = array();
		foreach($departments as $department) {
			$button = '<button id="RowBtnDelete-'.$department->id.'" type="button" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span></button>';
			$json['aaData'][] = [$department->title, $institution->title, $button];
		}
		$json['sEcho'] = 10;
		$json['iTotalRecords'] = $departments->count();
		$json['iTotalDisplayRecords'] = $departments->count();
		return response()->json($json);
	}

    /**
     * @param $id
     * @return mixed
     */
	public function adminDepartmentFromID($id)
	{
		$institution = Institution::findOrFail($id);
		$adminDepartment = $institution->departments()->where('slug', 'admin')->first();
		return $adminDepartment;
	}
	
}
