<div class="modal fade" id="RequestRoleChangeModal" tabindex="-1" role="dialog" aria-labelledby="RequestRoleChangeModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{trans('site.roleChangeRequest')}}</h4>
            </div> <!-- .modal-header -->
            <div class="modal-body">
                <form method="POST" action="/users/request_role_change" accept-charset="UTF-8" class="form-horizontal" role="form" enctype="multipart/form-data">
                    {{csrf_field()}}
                    @if ($errors->any() && !$errors->has('delete_account_confirmation_email'))
                        <div class="alert alert-danger">
                            <ul>
                                <strong>{{trans('users.changesNotSaved')}}</strong>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="form-group">
                        {!! Form::label('FieldCoordDepartSurname', trans('site.lastName').':', ['class' => 'control-label col-sm-4 ']) !!}
                        <div class="col-sm-8 form-control-static">
                            {!! Form::text('application_last_name',$user->lastname, ['readonly'=>true,'class' => 'form-control', 'placeholder' => trans('site.phoneRequired'), 'id' => 'FieldLastName']) !!}
                            <div class="help-block with-errors" style="margin:0px;"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('FieldCoordDepartΝame', trans('site.firstName').':', ['class' => 'control-label col-sm-4 ']) !!}
                        <div class="col-sm-8 form-control-static">
                            {!! Form::text('application_first_name',$user->firstname, ['readonly'=>true,'class' => 'form-control', 'placeholder' => trans('site.phoneRequired'), 'id' => 'FieldFirstName']) !!}
                            <div class="help-block with-errors" style="margin:0;"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('FieldUserEmail', 'Email:', ['class' => 'control-label col-sm-4 ']) !!}
                        <div class="col-sm-8 form-control-static">
                            {!! Form::text('application_email',$user->email, ['readonly'=>true,'class' => 'form-control', 'placeholder' => trans('site.phoneRequired'), 'id' => 'FieldEmail']) !!}
                            <div class="help-block with-errors" style="margin:0;"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('FieldCoordDepartPhone', trans('site.phone').':', ['class' => 'control-label col-sm-4 ']) !!}
                        <div class="col-sm-8">
                            @if(!empty($user->telephone))
                            {!! Form::text('application_telephone',$user->telephone, ['readonly'=>true,'class' => 'form-control', 'placeholder' => trans('site.phoneRequired'), 'id' => 'FieldPhone']) !!}
                            @else
                            {!! Form::text('application_telephone',null, ['class' => 'form-control', 'placeholder' => trans('site.phoneRequired'), 'id' => 'FieldPhone']) !!}
                            @endif
                            <div class="help-block with-errors" style="margin:0px;"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('FieldCoordDepartRole', trans('site.role').':', ['class' => 'control-label col-sm-4']) !!}
                        <div class="col-sm-8">
                            <div class="input-group">
                                @if($user->hasRole('DepartmentAdministrator'))
                                    {!! Form::select('application_role', ['' => ''] + ['InstitutionAdministrator' => trans('site.institutionModerator')], null, ['id' => 'FieldCoordDepartRole', 'style' => 'width: 100%', 'aria-describedby' => 'helpBlockRole'])!!}
                                @else
                                    {!! Form::select('application_role', ['' => ''] + ['InstitutionAdministrator' => trans('site.institutionModerator'), 'DepartmentAdministrator' => trans('site.departmentModerator')], null, ['id' => 'FieldCoordDepartRole', 'style' => 'width: 100%', 'aria-describedby' => 'helpBlockRole'])!!}
                                @endif
                                <span id="helpBlockRole" class="help-block" style="text-align: left;">{{trans('site.selectRole')}}</span>
                            </div>
                        </div>
                    </div>
                    <h4 style=" padding-top:15px; padding-bottom:5px; border-bottom: 1px solid #bcbcbc"><span class="glyphicon glyphicon-wrench"></span>  {{trans('site.moderateConferencesFor')}}:</h4>
                    <div class="form-group">
                        {!! Form::label('FieldCoordDepartOrg', trans('site.institution').':', ['class' => 'control-label col-sm-4 ']) !!}
                        <div class="col-sm-8 form-control-static">
                            @if($user->hasRole('DepartmentAdministrator'))
                            {{ $institution->title }}
                            <input type="hidden" name="institution_id" value="{{$institution->id}}">
                            @else
                             {!! Form::select('institution_id', ['' => ''] + App\Institution::pluck("title","id")->toArray(), $institution->id, ['id' => 'FieldRoleChangeInstitutionId', 'style' => 'width: 100%', 'aria-describedby' => 'helpBlockRole'])!!}
                            @endif
                            <div class="help-block with-errors" style="margin:0;"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('FieldCoordDepartDepart', trans('site.department').':', ['class' => 'control-label col-sm-4 ']) !!}
                        <div class="col-sm-8 form-control-static">
                            {!! Form::select('department_id', ['' => ''] + App\Department::where('institution_id',old('institution_id',$institution->id))->pluck("title","id")->toArray(), $department->id, ['id' => 'FieldRoleChangeDepartmentId', 'style' => 'width: 100%', 'aria-describedby' => 'helpBlockRole'])!!}
                        </div>
                    </div>
                    <div class="form-group" id="FieldCoordDepartDepartFormGroup">
                        {!! Form::label('FieldCoordDepartComment', trans('site.description').':', ['class' => 'control-label col-sm-4 ']) !!}
                        <div class="col-sm-8">
                            {!! Form::textarea('application_comment', null, ['class' => 'form-control', 'placeholder' => trans('site.justification'), 'id' => 'FieldCoordDepartComment', 'rows' => '3'])!!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('FieldCoordDepartTerms', trans('site.termsAcceptance').':', ['class' => 'control-label col-sm-4 ']) !!}
                        <div class="col-sm-1">
                            {!! Form::checkbox('accept_terms', 0, false, ['id' => 'FieldCoordDepartTerms', 'data-toggle' => 'checkbox-x', 'data-size' => 'lg', 'data-three-state' => 'false']) !!}
                        </div>
                        <a data-toggle="modal" href="#termsConditions" class="col-sm-7">{{trans('terms.ModeratorsTermsTextTitle')}}</a>
                    </div>
                    <div class="modal-footer" style="margin-top:0;">
                        {!! Form::submit(trans('site.save'), ['class' => 'btn btn-primary', 'id' => 'CoordDepartSubmitBtnNew', 'name' => 'CoordDepartSubmitBtnNew']) !!}
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{trans('site.cancel')}}</button>
                    </div> <!-- .modal-footer -->
                </form>
            </div> <!-- .modal-content -->
        </div> <!-- .modal-dialog -->
    </div> <!-- .modal -->
</div>