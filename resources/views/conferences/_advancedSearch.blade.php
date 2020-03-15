<div class="col-md-12 col-sm-12 col-xs-12 collapse" id="collapseAdvancedDearch">
    <div class="small-gap"></div>
    <div class="well">
        <h4>{!!trans('conferences.advancedSearch')!!}<span class="glyphicon glyphicon-search" aria-hidden="true"></span>
        </h4>
        <hr/>
        {!! Form::open(array('url' => '/conferences/all', 'method' => 'get', 'class' => 'form-horizontal', 'id' => 'CoordOrgForm', 'role' => 'form')) !!}
        <div class="row">
            <h4>{{trans('conferences.conferenceDetails')}}</h4>
            <hr/>
        </div>
        <div class="row">
            <div class="col-sm-4">
                {!! Form::text('id', Input::get('id'), ['class' => 'form-control', 'placeholder' => trans('conferences.conferenceID'), 'id' => 'searchID']) !!}
            </div>
            <div class="col-sm-4">
                {!! Form::text('title', Input::get('title'), ['class' => 'form-control', 'placeholder' => trans('conferences.title'), 'id' => 'searchTitle']) !!}
            </div>
            <div class="col-sm-4">
                {!! Form::select('invisible', ['' => ''] + [1 => 'Κρυφή', 0 => 'Ορατή'], Input::get('invisible'), ['id' => 'searchInvisible', 'style' => 'width: 100%'])!!}
            </div>
        </div>
        <div class="small-gap"></div>
        <div class="row">
            @if(Auth::user()->hasRole('InstitutionAdministrator') && !Auth::user()->hasRole('SuperAdmin') || Auth::user()->hasRole('DepartmentAdministrator'))
                <div class="col-sm-4">
                    {!! Form::select('institution_id', ['' => ''] + App\Institution::orderBy('title')->pluck('title', 'id')->toArray(), Auth::user()->institutions()->first()->id, ['id' => 'searchInstitution', 'style' => 'width: 100%','disabled'=>'disabled'])!!}
                </div>
            @else
                <div class="col-sm-4">
                    {!! Form::select('institution_id', ['' => ''] + App\Institution::orderBy('title')->pluck('title', 'id')->toArray(), Input::get('institution_id'), ['id' => 'searchInstitution', 'style' => 'width: 100%'])!!}
                </div>
            @endif
            @if(!empty(Input::get('department_id')) && !Auth::user()->hasRole('DepartmentAdministrator'))
                <div class="col-sm-4">
                    {!! Form::select('department_id', ['' => ''] + App\Department::where('institution_id', Input::get('institution_id'))->orderBy('title')->pluck('title', 'id')->toArray(), Input::get('department_id'), ['id' => 'searchDepartment', 'style' => 'width: 100%'])!!}
                </div>
            @elseif(Auth::user()->hasRole('DepartmentAdministrator'))
                <div class="col-sm-4">
                    {!! Form::select('department_id', ['' => '']+App\Department::where('institution_id', Auth::user()->institutions()->first()->id)->orderBy('title')->pluck('title', 'id')->toArray(), Auth::user()->departments()->first()->id, ['id' => 'searchDepartment', 'style' => 'width: 100%','disabled'=>'disabled'])!!}
                </div>
            @else
                <div class="col-sm-4">
                    {!! Form::select('department_id', ['' => ''], Input::get('department_id'), ['id' => 'searchDepartment', 'style' => 'width: 100%'])!!}
                </div>
            @endif
        </div>
        <div class="small-gap"></div>
        <div class="row">
            <div class="col-sm-4">
                {!! Form::label('searchStartFrom', 'Ημ/νία Έναρξης από:', ['class' => 'control-label']) !!}
                <div class="input-group date datepicker" style="width:250px">
                    {!! Form::text('start_from', Input::get('start_from'), ['class' => 'form-control', 'id' => 'searchStartFrom']) !!}
                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                </div>
            </div>

            <div class="col-sm-4">
                {!! Form::label('searchStartΤο', 'Έως:', ['class' => 'control-label']) !!}
                <div class="input-group date datepicker" style="width:250px">
                    {!! Form::text('start_to', Input::get('start_to'), ['class' => 'form-control', 'id' => 'searchStartΤο']) !!}
                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                </div>
            </div>
            <div class="col-sm-4">
                {!! Form::label('created_at', 'Ημερομηνία Δημιουργίας:', ['class' => 'control-label']) !!}
                <div class="input-group date datepicker" style="width:250px">
                    {!! Form::text('created_at', Input::get('created_at'), ['class' => 'form-control', 'id' => 'searchCreatedAt']) !!}
                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                </div>
            </div>
        </div>
        <div class="small-gap"></div>
        <div class="row">
            <h4>{{trans('conferences.moderatorDetails')}}</h4>
            <hr/>
        </div>
        <div class="row">
            <div class="col-sm-4">
                {!! Form::text('firstname', Input::get('firstname'), ['class' => 'form-control', 'placeholder' => trans('conferences.name'), 'id' => 'searchFirstname']) !!}
            </div>
            <div class="col-sm-4">
                {!! Form::text('lastname', Input::get('lastname'), ['class' => 'form-control', 'placeholder' => trans('conferences.surname'), 'id' => 'searchLastname']) !!}
            </div>
            @if(Auth::user()->hasRole('SuperAdmin') || Auth::user()->hasRole('InstitutionAdministrator'))
                <div class="col-sm-4">
                    {!! Form::text('email', Input::get('email'), ['class' => 'form-control', 'placeholder' => 'Email', 'id' => 'searchEmail']) !!}
                </div>
            @endif
        </div>
        <div class="small-gap"></div>
        <div>
            {!! Form::submit(trans('conferences.search'), ['class' => 'btn btn-primary', 'id' => 'UserSubmitBtnNew', 'name' => 'advancedSearch']) !!}
        </div>
        {!! Form::close() !!}

    </div>
</div>
