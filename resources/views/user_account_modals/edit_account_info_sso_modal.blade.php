<div class="modal fade" id="UserModal" tabindex="-1" role="dialog" aria-labelledby="UserModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="UserLabel">{{trans('site.accountDetails')}}</h4>
            </div> <!-- .modal-header -->
            <div class="modal-body">
                {!! Form::open(array('url' => ['account/update_sso'], 'method' => 'PATCH', 'class' => 'form-horizontal', 'id' => 'OrgForm', 'role' => 'form', 'files' => true)) !!}
                {!! Form::hidden('role', $role->name) !!}
                @if ($errors->any() && (!$errors->has('delete_account_confirmation_email') && !$errors->has('confirmation_email_not_matched')  && !$errors->has('application_comment')  && !$errors->has('accept_terms') && !$errors->has('application_role') && !$errors->has('application_telephone') ))
                    <div class="alert alert-danger">
                        <ul>
                            <strong>{{trans('users.changesNotSaved')}}</strong>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                {{--Basic info section start--}}

                <div class="form-group">
                    {!! Form::label('FieldUserSurname', trans('users.surname').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        {!! Form::text('lastname', $user->lastname, ['class' => 'form-control','id' => 'FieldUserSurname', 'placeholder' => trans('users.surnameRequired')]) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('FieldUserΝame', trans('users.name').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        {!! Form::text('firstname', $user->firstname, ['class' => 'form-control', 'id' => 'FieldUserΝame', 'placeholder' => trans('users.nameRequired')]) !!}
                        <div class="help-block with-errors" style="margin:0px;"></div>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('FieldUserEmail', 'Email:', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8 form-control-static">
                        {{ $user->email }}
                        <div class="alert alert-warning help-block"
                             role="alert">{!!trans('users.ExtraEmailsWarningPersonal')!!}</div>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('FieldUserPhone', trans('users.telephone').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        @if(!$user->hasRole('EndUser'))
                            {!! Form::text('telephone', $user->telephone, ['class' => 'form-control', 'id' => 'FieldUserPhone', 'placeholder' => trans('users.telephoneRequired')]) !!}
                        @else
                            {!! Form::text('telephone', $user->telephone, ['class' => 'form-control', 'id' => 'FieldUserPhone', 'placeholder' => trans('users.telephoneOptional')]) !!}
                        @endif
                        <div class="help-block with-errors" style="margin:0px;"></div>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('FieldUserImage', trans('users.uploadPhoto').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8 form-control-static">
                        @if(!empty($user->thumbnail))
                            <div class="card">
                                <img src="/images/user_images/{{ $user->thumbnail }}" class="card-img-top img-thumbnail"
                                     alt="Responsive image">

                                <div class="card-block">
                                    <a href="#" id="deleteMyUserImage"
                                       class="card-link">{{trans('users.deletePhoto')}}</a>
                                </div>
                                <div class="small-gap"></div>
                            </div>
                        @endif
                        {!! Form::file('thumbnail', ['id' => 'FieldUserImage']) !!}
                        <p class="help-block">{{trans('users.acceptedFileTypes')}}: jpeg, png, bmp, gif,
                            svg. {{trans('users.maxFileSize')}}: 300kB.</p>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('FieldUseRole', trans('users.role').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8 form-control-static" id="FieldUserRole">
                        {{ trans($role->label) }}
                    </div>
                </div>

                {{--Basic info section end--}}

                {{--Institution section start--}}

                <div class="form-group">
                    {!! Form::label('FieldUserOrg', trans('users.institution').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8 form-control-static">
                            {{ $institution->title }}
                    </div>
                    <input type="hidden" name="institution_id" value="{{$institution->id}}">
                </div>

                {{--Institution section end--}}

                {{--Department section start--}}

                @if(!$user->hasRole('SuperAdmin') && !$user->hasRole('DepartmentAdministrator'))
                    <div class="form-group" id="DepContainer">
                        {!! Form::label('FieldUserDepart', trans('users.department').':', ['class' => 'control-label col-sm-4']) !!}
                        <div class="col-sm-8">
                         {!! Form::select('department_id', ['' => ''] + App\Department::where('institution_id', $institution->id)->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], $department->id , ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
                        </div>
                    </div>
                    <div class="form-group" id="NewDepContainer">
                        {!! Form::label('FieldUserDepartNew', trans('users.newDepartment').':', ['class' => 'control-label col-sm-4']) !!}
                        <div class="col-sm-8">
                            {!! Form::text('new_department', null, ['class' => 'form-control', 'placeholder' => trans('users.enterDepartmentRequired'), 'id' => 'FieldUserDepartNew']) !!}
                                <div id="newDepDiv" class="help-block with-errors newdep alert alert-warning"
                                     style="margin:0;">{{ trans('users.newDeptWarning') }}</div>
                        </div>
                    </div>
                @else
                    <div class="form-group">
                        {!! Form::label('FieldUserDepart', trans('users.department').':', ['class' => 'control-label col-sm-4']) !!}
                        <div class="col-sm-8 form-control-static">
                            {{ $department->title }}
                        </div>
                    </div>
                @endif
                <div class="modal-footer" style="margin-top:0;">
                    {!! Form::submit(trans('site.save'), ['class' => 'btn btn-primary', 'id' => 'UserSubmitBtnNew', 'name' => 'add_new']) !!}
                    <button type="button" id="UserModalButtonClose"
                            class="btn btn-default">{{trans('site.cancel')}}</button>
                </div> <!-- .modal-footer -->
            </div> <!-- .modal-content -->
            {!! Form::close() !!}
        </div> <!-- .modal-dialog -->
    </div> <!-- .modal -->
</div>