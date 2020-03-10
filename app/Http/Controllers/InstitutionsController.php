<?php

namespace App\Http\Controllers;

use App\Domain;
use Gate;
use Auth;
use App\Institution;
use App\Department;
use App\Conference;
use Carbon\Carbon;
use Request;
use Input;
use Log;

use App\Http\Requests;
use App\Http\Controllers\Controller;

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
	
	public function create()
	{
		if (Gate::denies('view_institutions')) {
            abort(403);
        }
		
		return view('institutions.create');
	}
	
	public function store(Requests\CreateInstitutionRequest $request)
	{
		if (Gate::denies('view_institutions')) {
            abort(403);
        }
		
		$input = $request->all();
		$input['created_at'] = Carbon::now();
		$input['updated_at'] = Carbon::now();
        $input['slug'] = 'NoID';

		if(isset($input['add_new'])){

			$institution = Institution::create($input);

			if(!empty($institution->shibboleth_domain)){
                $domain = new Domain;
                $domain->institution_id = $institution->id;
                $domain->name = $institution->shibboleth_domain;
                $domain->save();
			}

			Department::create(['title' => trans('controllers.administration'), 'slug' => 'admin', 'institution_id' => $institution->id, 'created_at' => $input['created_at'], 'updated_at' => $input['updated_at']]);
			Department::create(['title' => trans('controllers.other'), 'slug' => 'other', 'institution_id' => $institution->id, 'created_at' => $input['created_at'], 'updated_at' => $input['updated_at']]);
		}
		
		return redirect('institutions')->with('storesSuccessfully', trans('controllers.newInstitutionSaved'));
	}
	
	public function edit($id)
	{
		if (Gate::denies('view_institutions')) {
            abort(403);
        }
		
		$institution = Institution::findOrFail($id);
		return view('institutions.edit', compact('institution'));
		
	}
	
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
     * @param null $department_id
     * @return string
     */
	public function listDepartments($id, $department_id = null)
	{
        $institution = Institution::findOrFail($id);
		$departments = $institution->departments()->orderBy('title')->get();
		$html = '<option value=""></option>';
		foreach($departments as $department){
            $html .= '<option value="'.$department->id.'">'.$department->title.'</option>';
		}
        $html .= '<option value="other">'.trans('controllers.other').'</option>';
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
	
	public function adminDepartmentFromID($id)
	{
		$institution = Institution::findOrFail($id);
		$adminDepartment = $institution->departments()->where('slug', 'admin')->first();
		
		return $adminDepartment;
	}
	
}
