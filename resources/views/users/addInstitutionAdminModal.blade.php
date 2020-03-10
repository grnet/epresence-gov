<!-- MODAL CoordOrg start -->
<div class="modal fade" id="InstitutionAdminModal" tabindex="-1" role="dialog" aria-labelledby="InstitutionAdminModal"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="InstitutionAdminLabel">{{trans('users.addInstitutionModerator')}}</h4>
            </div> <!-- .modal-header -->
            <div class="modal-body">
                <div class="alert alert-danger" id="matched_error_container_inst">
                    <strong>{{trans('users.email_matched_to_organisation')}} </strong>
                </div>
                <div class="alert alert-danger" id="not_matched_error_container_inst">
                    <strong>{{trans('users.email_not_matched_to_organisation')}} </strong>
                </div>
                {!! Form::open(array('url' => 'store_institution_admin', 'method' => 'post', 'class' => 'form-horizontal', 'id' => 'addNewInstitutionAdminForm', 'role' => 'form')) !!}

                <div class="form-group">
                    {!! Form::label('FieldInstitutionAdminSurname', trans('users.surname').':', ['class' => 'control-label col-sm-4 ']) !!}
                    <div class="col-sm-8">
                        {!! Form::text('inst_admin_lastname', null, ['class' => 'form-control', 'placeholder' => trans('users.surnameOptional'), 'id' => 'FieldInstitutionAdminSurname']) !!}
                        <div class="help-block with-errors" style="margin:0"></div>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('FieldInstitutionAdminName', trans('users.name').':', ['class' => 'control-label col-sm-4 ']) !!}
                    <div class="col-sm-8">
                        {!! Form::text('inst_admin_firstname', null, ['class' => 'form-control', 'placeholder' => trans('users.nameOptional'), 'id' => 'FieldInstitutionAdminName']) !!}
                        <div class="help-block with-errors" style="margin:0;"></div>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('FieldInstitutionAdminEmail', 'Email:', ['class' => 'control-label col-sm-4 ']) !!}
                    <div class="col-sm-8">
                        {!! Form::text('inst_admin_email', null, ['class' => 'form-control', 'placeholder' => trans('users.emailRequired'), 'id' => 'FieldInstitutionAdminEmail']) !!}
                        <div class="help-block with-errors" style="margin:0"></div>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('FieldInstitutionAdminPhone', trans('users.telephone').':', ['class' => 'control-label col-sm-4 ']) !!}
                    <div class="col-sm-8">
                        {!! Form::text('inst_admin_telephone', null, ['class' => 'form-control', 'placeholder' => trans('users.telephoneRequired'), 'id' => 'FieldInstitutionAdminPhone']) !!}
                        <div class="help-block with-errors" style="margin:0"></div>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('FieldAdminRoleStatic', trans('users.role').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8 form-control-static">
                        {{trans('users.institutionModerator')}}
                    </div>
                </div>
                <h4 style=" padding-top:15px; padding-bottom:5px; border-bottom: 1px solid #bcbcbc"><span
                            class="glyphicon glyphicon-wrench"></span> {{trans('users.manageConferencesFor')}}:</h4>
                <div class="form-group">
                    {!! Form::label('FieldInstitutionAdminOrg', trans('users.institution').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        {!! Form::select('inst_admin_institution_id', ['' => ''] + App\Institution::orderBy('title')->pluck('title', 'id')->toArray(), null, ['id' => 'FieldInstitutionAdminOrg', 'style' => 'width: 100%'])!!}
                    </div>
                </div>
                <div class="form-group" id="InstitutionAdminDepartContainer">
                    {!! Form::label('FieldInstitutionAdminDepart', trans('users.department').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        @if(Input::old('inst_admin_institution_id') && Input::old('inst_admin_institution_id')!== 'other' )
                            {!! Form::select('inst_admin_department_id',['' => ''] + App\Department::where('institution_id',Input::old('inst_admin_institution_id'))->orderBy('title')->pluck('title', 'id')->toArray(), null, ['id' => 'FieldInstitutionAdminDepart', 'style' => 'width: 100%'])!!}
                        @else
                            {!! Form::select('inst_admin_department_id', ['' => ''], null, ['id' => 'FieldInstitutionAdminDepart', 'style' => 'width: 100%'])!!}
                        @endif
                    </div>
                </div>
                <div class="form-group" id="InstitutionAdminDepNewContainer">
                    {!! Form::label('InstitutionAdminDepNewField', trans('users.newDepartment').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        {!! Form::text('inst_admin_new_department', null, ['class' => 'form-control', 'placeholder' => trans('users.enterDepartment'), 'id' => 'InstitutionAdminDepNewField']) !!}
                        <div class="help-block with-errors newdep alert alert-warning"
                             style="margin:0px;">{{ trans('users.newDeptWarning') }}</div>
                    </div>
                </div>
                {!! Form::hidden('from', URL::full()) !!}
                <div class="modal-footer" style="margin-top:0px;">
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