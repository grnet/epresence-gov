<div class="col-md-12 col-sm-12 col-xs-12 collapse" id="collapseAdvancedSearch">
    <div class="small-gap"></div>
    <div class="well">
        <h4>{{trans('users.advancedSearch')}} <span class="glyphicon glyphicon-search" aria-hidden="true"></span></h4>
        <hr/>

        {!! Form::open(array('url' => Request::fullUrl(), 'method' => 'get', 'class' => 'form-horizontal', 'id' => 'CoordOrgForm', 'role' => 'form')) !!}

        <div class="row">
            <div class="col-sm-3">
                {!! Form::text('firstname', Input::get('firstname'), ['class' => 'form-control c_val', 'placeholder' => trans('users.name'), 'id' => 'searchFirstname']) !!}
            </div>
            <div class="col-sm-3">
                {!! Form::text('lastname', Input::get('lastname'), ['class' => 'form-control c_val', 'placeholder' => trans('users.surname'), 'id' => 'searchLastname']) !!}
            </div>
           <div class="col-sm-3">
                {!! Form::text('email', Input::get('email') , ['class' => 'form-control c_val', 'placeholder' => 'Email', 'id' => 'searchEmail']) !!}
            </div>
            <div class="col-sm-3">
                {!! Form::text('telephone', Input::get('telephone'), ['class' => 'form-control c_val', 'placeholder' => trans('users.telephone'), 'id' => 'searchPhone']) !!}
            </div>
        </div>
        <div class="small-gap"></div>
        <div class="row">
            @if(Auth::user()->hasRole('InstitutionAdministrator'))
                <div class="col-sm-3">
                    {!! Form::select('institution', ['' => ''] + App\Institution::orderBy('title')->pluck('title', 'id')->toArray(), Auth::user()->institutions()->first()->id, ['id' => 'searchInstitution', 'style' => 'width: 100%','disabled'=>'disabled'])!!}
                </div>
            @else
                <div class="col-sm-3">
                    {!! Form::select('institution', ['' => ''] + App\Institution::orderBy('title')->pluck('title', 'id')->toArray(), Input::get('institution'), ['id' => 'searchInstitution', 'style' => 'width: 100%'])!!}
                </div>
            @endif
            @if(!empty(Input::get('department')))
                <div class="col-sm-3">
                    {!! Form::select('department', ['' => ''] + App\Department::where('institution_id', Input::get('institution'))->orderBy('title')->pluck('title', 'id')->toArray(), Input::get('department'), ['id' => 'searchDepartment', 'style' => 'width: 100%','class'=>'c_val'])!!}
                </div>
            @else
                @if(empty(Input::get('institution')))
                    <div class="col-sm-3">
                        {!! Form::select('department', ['' => ''], Input::get('department'), ['id' => 'searchDepartment', 'style' => 'width: 100%','class'=>'c_val'])!!}
                    </div>
                @else
                    <div class="col-sm-3">
                        {!! Form::select('department', ['' => ''] + App\Department::where('institution_id', Input::get('institution'))->orderBy('title')->pluck('title', 'id')->toArray(),null, ['id' => 'searchDepartment', 'style' => 'width: 100%','class'=>'c_val'])!!}
                    </div>
                @endif
            @endif
            <div class="col-sm-3">
                {!! Form::select('confirmed', ['' => '',1=>trans("users.yes"),0=>trans("users.no")], Input::get('confirmed'), ['id' => 'confirmedFilter', 'style' => 'width: 100%','class'=>'c_val'])!!}
            </div>
            <div class="col-sm-3">
                {!! Form::select('accepted_terms', ['' => '',1=>trans("users.yes"),0=>trans("users.no")], Input::get('accepted_terms'), ['id' => 'acceptedTermsFilter', 'style' => 'width: 100%','class'=>'c_val'])!!}
            </div>
        </div>

        <div class="small-gap"></div>

        <div class="row">
            <div class="col-sm-3">
                {!! Form::select('state', ['' => ''] + ['local' => trans('users.yes'), 'sso' => trans('users.no')], Input::get('state'), ['id' => 'searchState', 'style' => 'width: 100%' ,'class'=>'c_val'])!!}
            </div>

            <div class="col-sm-3">
                {!! Form::select('status', ['' => ''] + [1 => trans('users.active'), 0 => trans('users.inactive')], Input::get('status'), ['id' => 'searchStatus', 'style' => 'width: 100%','class'=>'c_val'])!!}
            </div>
            <div class="col-sm-3">
                {!! Form::select('multi_mails', ['' => ''] + [1 => trans('users.yes'), 0 => trans('users.no')], Input::get('multi_mails'), ['id' => 'searchMultiMails', 'style' => 'width: 100%','class'=>'c_val'])!!}
            </div>

            @if(Request::path() == 'users')
                <div class="col-sm-3">
                    {!! Form::select('role', ['' => ''] + App\User::role_dropdown(['SuperAdmin'], 'id'), Input::get('role'), ['id' => 'searchRole', 'style' => 'width: 100%','class'=>'c_val'])!!}
                </div>

            @elseif(Request::path() == 'administrators')
                <div class="col-sm-3">
                    {!! Form::select('role', ['' => ''] + App\User::role_dropdown(['EndUser'], 'id'), Input::get('role'), ['id' => 'searchRole', 'style' => 'width: 100%','class'=>'c_val'])!!}
                </div>

            @endif

        </div>

        <div class="small-gap"></div>

        <div class="row">
            <div class="col-sm-4">
                {!! Form::label('searchCreatedAtFrom', trans('users.createdAtFrom').':', ['class' => 'control-label']) !!}
                <div class="input-group date datepicker" style="width:250px">
                    {!! Form::text('created_at_from', Input::get('created_at_from'), ['class' => 'form-control c_val', 'id' => 'searchCreatedAtFrom']) !!}
                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                </div>
            </div>

            <div class="col-sm-4">
                {!! Form::label('searchCreatedAtTo', trans('users.createdAtTo').':', ['class' => 'control-label']) !!}
                <div class="input-group date datepicker" style="width:250px">
                    {!! Form::text('created_at_to', Input::get('created_at_to'), ['class' => 'form-control c_val', 'id' => 'searchCreatedAtTo']) !!}
                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                </div>
            </div>
        </div>
        <div class="small-gap"></div>
        <div>
            {!! Form::submit(trans('users.search'), ['class' => 'btn btn-primary', 'id' => 'UserSubmitBtnNew' ,'name'=>'search']) !!}
            @if(Auth::user()->hasRole("SuperAdmin"))
                {!! Form::submit('Export', ['class' => 'btn btn-primary', 'id' => 'UserSubmitBtnNew' ,'name'=>'export']) !!}
            @endif
        </div>
        {!! Form::close() !!}
    </div>
</div>
