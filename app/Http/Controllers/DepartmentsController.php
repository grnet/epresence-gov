<?php

namespace App\Http\Controllers;

use Gate;
use App\Department;
use App\Institution;
use App\Conference;
use Carbon\Carbon;
use Request;
use Input;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class DepartmentsController extends Controller
{
	public function __construct()
	{
    $this->middleware('auth');
	}
	
    public function index($id)
	{
		
		if (Gate::denies('view_institutions')) {
            abort(403);
        }
		
		// Limit
		$limit = Input::get('limit') ?: 10;
		
		$institution = Institution::findOrFail($id);
		
		if ($institution->departments->count() == 0) {
            abort(404);
        }
		
		$departments_default = Department::where('institution_id', $id)->where('slug', '!=', 'other');
		
		$departments_default = Institution::advancedSearch($departments_default, Input::all());
		
		$departments = $departments_default->paginate($limit);
		
		return view('departments.index', compact('departments'));
	}
	
	public function show($id)
	{
		$department = Department::findOrFail($id);
		
		// Session::put('previous_url', URL::previous());
		
		return redirect("/departments/".$id."/edit");
	}
	
	public function create()
	{
		return view('departments.create');
	}
	
	public function store(Requests\CreateDepartmentRequest $request)
	{
		$input = $request->all();
		$input['created_at'] = Carbon::now();
		$input['updated_at'] = Carbon::now();
        $input['slug'] = "NoID";
		
		$department = Department::create($input);
		
		return redirect("/institutions/".$department->institution_id."/departments")->with('storesSuccessfully', trans('controllers.newDepartmentSaved'));
	}
	
	public function edit($id)
	{
		
		$department = Department::findOrFail($id);
		return view('departments.edit', compact('department'));
		
	}
	
	public function delete($id)
	{
		if (Gate::denies('view_institutions')) {
            abort(403);
        }
		
		$department = Department::findOrFail($id);
		if($department->users()->count() > 0){
			$results = array(
					'status' => 'error',
					'data' => trans('controllers.cannotDeleteDepartment')
				);
		}else{
			$department->delete();
		
		$results = array(
					'status' => 'success',
					'data' => trans('controllers.departmentDeleted')
				);
		}
		echo json_encode($results);
	}
	
	public function update(Requests\CreateDepartmentRequest $request, $id)
	{
		$department = Department::findOrFail($id);
		$input = $request->all();
		
		$input['updated_at'] = Carbon::now();
		
		$department->update($input);
		
		return redirect("/institutions/".$department->institution_id."/departments")->with('storesSuccessfully', trans('controllers.changesSaved'));
	}
}
