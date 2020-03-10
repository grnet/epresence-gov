<!-- MODAL CoordOrg start -->
<div class="modal fade" id="DepartmentAdminModal" tabindex="-1" role="dialog"
     aria-labelledby="DepartmentAdminModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="DepartmentAdminLabel">{{trans('users.addDepartmentModerator')}}</h4>

            </div> <!-- .modal-header -->
            <div class="modal-body">

                <div class="alert alert-danger" id="matched_error_container_dept">
                    <strong>{{trans('users.email_matched_to_organisation')}} </strong>
                </div>
                <div class="alert alert-danger" id="not_matched_error_container_dept">
                    <strong>{{trans('users.email_not_matched_to_organisation')}} </strong>
                </div>
                {!! Form::open(array('url' => 'store_department_admin', 'method' => 'post', 'class' => 'form-horizontal', 'role' => 'form','id'=>'addNewDepartmentAdminForm')) !!}

                <div class="form-group">
                    {!! Form::label('FieldDepartmentAdminSurname', trans('users.surname').':', ['class' => 'control-label col-sm-4 ']) !!}
                    <div class="col-sm-8">
                        {!! Form::text('dept_admin_lastname', null, ['class' => 'form-control', 'placeholder' => trans('users.surnameOptional'),'id'=>'FieldDepartmentAdminSurname']) !!}
                        <div class="help-block with-errors" style="margin:0px;"></div>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('FieldDepartmentAdminΝame', trans('users.name').':', ['class' => 'control-label col-sm-4 ']) !!}
                    <div class="col-sm-8">
                        {!! Form::text('dept_admin_firstname', null, ['class' => 'form-control', 'placeholder' => trans('users.nameOptional'),'id'=>'FieldDepartmentAdminΝame']) !!}
                        <div class="help-block with-errors" style="margin:0px;"></div>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('FieldDepartmentAdminEmail', 'Email:', ['class' => 'control-label col-sm-4 ']) !!}
                    <div class="col-sm-8">
                        {!! Form::text('dept_admin_email', null, ['class' => 'form-control', 'placeholder' => trans('users.emailRequired'),'id'=>'FieldDepartmentAdminEmail']) !!}
                        <div class="help-block with-errors" style="margin:0px;"></div>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('FieldDepartmentAdminPhone', trans('users.telephone').':', ['class' => 'control-label col-sm-4 ']) !!}
                    <div class="col-sm-8">
                        {!! Form::text('dept_admin_telephone', null, ['class' => 'form-control', 'placeholder' => trans('users.telephoneRequired') , 'id'=>'FieldDepartmentAdminPhone']) !!}
                        <div class="help-block with-errors" style="margin:0px;"></div>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('FieldAdminRoleStatic', trans('users.role').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8 form-control-static">
                        {{trans('users.departmentModerator')}}
                    </div>
                </div>

                <h4 style=" padding-top:15px; padding-bottom:5px; border-bottom: 1px solid #bcbcbc"><span
                            class="glyphicon glyphicon-wrench"></span> {{trans('users.manageConferencesFor')}}
                    :</h4>

                @if(Auth::user()->hasRole('SuperAdmin'))
                    <div class="form-group">
                        {!! Form::label('FieldDepartmentAdminOrg', trans('users.institution').':', ['class' => 'control-label col-sm-4']) !!}
                        <div class="col-sm-8">
                            {!! Form::select('dept_admin_institution_id', ['' => ''] + App\Institution::orderBy('title')->pluck('title', 'id')->toArray(), null, ['id' => 'FieldDepartmentAdminOrg', 'style' => 'width: 100%'])!!}
                        </div>
                    </div>

                    <div class="form-group" id="DepartmentAdminOrgNewContainer">
                        {!! Form::label('DepartmentAdminOrgNewField', trans('users.newInstitution').':', ['class' => 'control-label col-sm-4']) !!}
                        <div class="col-sm-8">
                            {!! Form::text('dept_admin_new_institution', null, ['class' => 'form-control', 'placeholder' => trans('users.enterInstitution'), 'id' => 'DepartmentAdminOrgNewField']) !!}
                            <div class="help-block with-errors" style="margin:0px;"></div>
                        </div>
                    </div>

                @elseif(Auth::user()->hasRole('InstitutionAdministrator'))
                    <div class="form-group">
                        {!! Form::label('FieldAdminOrgStatic', trans('users.institution').':', ['class' => 'control-label col-sm-4']) !!}
                        <div class="col-sm-8 form-control-static">
                            {{ Auth::user()->institutions->first()->title }}
                        </div>
                    </div>

                    {!! Form::hidden('dept_admin_institution_id', Auth::user()->institutions->first()->id) !!}
                @endif

                <div class="form-group" id="DepartmentAdminDepartContainer">
                    {!! Form::label('FieldDepartmentAdminDepart', trans('users.department').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        @if(Auth::user()->hasRole('SuperAdmin'))
                         @if(Input::old('dept_admin_institution_id') && Input::old('dept_admin_institution_id')!== 'other' )
                                 {!! Form::select('dept_admin_department_id',['' => ''] + App\Department::where('institution_id', Input::old('dept_admin_institution_id'))->orderBy('title')->pluck('title', 'id')->toArray(), null, ['id' => 'FieldDepartmentAdminDepart', 'style' => 'width: 100%'])!!}
                             @else
                                {!! Form::select('dept_admin_department_id',['' => ''] , null, ['id' => 'FieldDepartmentAdminDepart', 'style' => 'width: 100%'])!!}
                         @endif
                        @else
                        {!! Form::select('dept_admin_department_id', ['' => ''] + App\Department::where('institution_id', Auth::user()->institutions->first()->id)->orderBy('title')->pluck('title', 'id')->toArray(), null, ['id' => 'FieldDepartmentAdminDepart', 'style' => 'width: 100%'])!!}
                        @endif
                    </div>
                </div>

                <div class="form-group" id="DepartmentAdminDepNewContainer">
                    {!! Form::label('DepartmentAdminDepNewField', trans('users.newDepartment').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        {!! Form::text('dept_admin_new_department', null, ['class' => 'form-control', 'placeholder' => trans('users.enterDepartment'), 'id' => 'DepartmentAdminDepNewField']) !!}
                        <div class="help-block with-errors newdep alert alert-warning"
                             style="margin:0;">{{ trans('users.newDeptWarning') }}</div>
                    </div>
                </div>
                {!! Form::hidden('from', URL::full()) !!}
                <div class="modal-footer" style="margin-top:0;">
                    {!! Form::submit(trans('users.save'), ['class' => 'btn btn-primary', 'id' => 'AdminSubmitBtnNew', 'name' => 'AdminSubmitBtnNew']) !!}
                    <button type="button" data-dismiss="modal" aria-hidden="true"
                            class="btn btn-default">{{trans('users.cancel')}}</button>
                </div> <!-- .modal-footer -->
                {!! Form::close() !!}
            </div> <!-- .modal-body -->
        </div> <!-- .modal-content -->
    </div> <!-- .modal-dialog -->
</div> <!-- .modal -->
<!-- modal Admin end -->