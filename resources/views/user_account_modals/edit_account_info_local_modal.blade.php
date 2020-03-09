<div class="modal fade" id="UserModal" tabindex="-1" role="dialog" aria-labelledby="UserModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="UserLabel">{{trans('site.accountDetails')}}</h4>
            </div> <!-- .modal-header -->
            <div class="modal-body">
                {!! Form::model($user, array('url' => ['account/update_local'], 'method' => 'PATCH', 'class' => 'form-horizontal', 'id' => 'OrgForm', 'role' => 'form', 'files' => true)) !!}


                {{--Used To simplify validations--}}

                {!! Form::hidden('role', $role->name) !!}

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
                    </div>
                </div>



                <div class="form-group">
                    {!! Form::label('FieldUseStatus', trans('users.localUserShort').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8 form-control-static">
                        {{ $user->state_string($user->state) }}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('FieldUserPhone', trans('users.telephone').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        @if(Auth::user()->hasRole('EndUser') == false)
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

                @if($user->hasRole('EndUser'))
                    <div class="form-group">
                        {!! Form::label('FieldUserOrg', trans('users.institution').':', ['class' => 'control-label col-sm-4']) !!}
                        <div class="col-sm-8">
                            @if($institution->slug == 'other')
                                {!! Form::select('institution_id', ['' => ''] + App\Institution::whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], 'other', ['id' => 'FieldUserOrg', 'style' => 'width: 100%'])!!}
                            @else
                                {!! Form::select('institution_id', ['' => ''] + App\Institution::whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], $institution->id, ['id' => 'FieldUserOrg', 'style' => 'width: 100%'])!!}
                            @endif
                        </div>
                    </div>
                    <div class="form-group" id="NewOrgContainer">
                        {!! Form::label('FieldUserOrgNew', trans('users.newInstitution').':', ['class' => 'control-label col-sm-4']) !!}
                        <div class="col-sm-8">

                            @if($institution->slug == 'other')
                                {!! Form::text('new_institution', $user->customValues()['institution'], ['class' => 'form-control', 'placeholder' => trans('users.enterInstitutionRequired'), 'id' => 'FieldUserOrgNew']) !!}
                            @else
                                {!! Form::text('new_institution', null, ['class' => 'form-control', 'placeholder' => trans('users.enterInstitutionRequired'), 'id' => 'FieldUserOrgNew']) !!}
                            @endif
                            <div class="help-block with-errors" style="margin:0px;"></div>
                        </div>
                    </div>
                @else
                    <div class="form-group">
                        {!! Form::label('FieldUserOrg', trans('users.institution').':', ['class' => 'control-label col-sm-4']) !!}
                        <div class="col-sm-8 form-control-static">
                            @if($institution->slug == 'other')
                                {{ $institution->title }} ({{ $user->customValues()['institution'] }})
                            @else
                                {{ $institution->title }}
                            @endif
                        </div>
                    </div>
                @endif

                {{--Institution section end--}}

                {{--Department section start--}}

                @if($user->hasRole('EndUser'))
                        <div class="form-group" id="DepContainer">
                            {!! Form::label('FieldUserDepart',trans('users.department').':', ['class' => 'control-label col-sm-4']) !!}
                            <div class="col-sm-8">
                                @if($department->slug == 'other')
                                    {!! Form::select('department_id', ['' => ''] + App\Department::whereNotIn('slug', ['other'])->where('institution_id', $institution->id)->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], 'other', ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
                                @else
                                    {!! Form::select('department_id', ['' => ''] + App\Department::where('institution_id', $institution->id)->whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], $department->id , ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
                                @endif
                            </div>
                        </div>
                        <div class="form-group" id="NewDepContainer">
                            {!! Form::label('FieldUserDepartNew', trans('users.newDepartment').':', ['class' => 'control-label col-sm-4']) !!}
                            <div class="col-sm-8">
                                @if($department->slug == 'other')
                                {!! Form::text('new_department', $user->customValues()['department'], ['class' => 'form-control', 'placeholder' => trans('users.enterDepartmentRequired'), 'id' => 'FieldUserDepartNew']) !!}
                                @else
                                    {!! Form::text('new_department', null, ['class' => 'form-control', 'placeholder' => trans('users.enterDepartmentRequired'), 'id' => 'FieldUserDepartNew']) !!}
                               @endif
                            </div>
                        </div>
                @else
                    <div class="form-group">
                        {!! Form::label('FieldUserDepart', trans('users.department').':', ['class' => 'control-label col-sm-4']) !!}
                        <div class="col-sm-8 form-control-static">
                            @if($department->slug == 'other')
                            {{ $department->title }} ({{ $user->customValues()['department'] }})
                            @else
                            {{ $department->title }}
                            @endif
                        </div>
                    </div>
                @endif

                {{--Department section end--}}

                {{--Change password section start--}}

                <div class="form-group">
                    {!! Form::label('FieldUserCurrentPassword','-', ['class' => 'control-label col-sm-4']) !!}
                    <p class="help-block col-sm-4">{{trans('users.info')}}</p>
                </div>
                <div class="form-group">
                    {!! Form::label('FieldUserCurrentPassword', trans('users.currentPassword').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        {!! Form::password('current_password', ['class' => 'form-control','id' => 'FieldUserCurrentPassword', 'placeholder' => trans('users.currentPasswordOptional')]) !!}
                        <div class="help-block with-errors" style="margin:0px;"></div>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('FieldUserPassword', trans('users.newPassword').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        {!! Form::password('password', ['class' => 'form-control','id' => 'FieldUserPassword', 'placeholder' => trans('users.newPasswordOptional')]) !!}
                        <div class="help-block with-errors" style="margin:0px;"></div>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('FieldUserConfPassword', trans('users.confirmPassword').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        {!! Form::password('password_confirmation', ['class' => 'form-control','id' => 'FieldUserConfPassword', 'placeholder' => trans('users.confirmPasswordOptional')]) !!}
                        <div class="help-block with-errors" style="margin:0px;"></div>
                    </div>
                </div>

                {{--Change password section end--}}

                <div class="modal-footer" style="margin-top:0px;">
                    {!! Form::submit(trans('site.save'), ['class' => 'btn btn-primary', 'id' => 'UserSubmitBtnNew']) !!}
                    <button type="button" id="UserModalButtonClose"
                            class="btn btn-default">{{trans('site.cancel')}}</button>
                </div> <!-- .modal-footer -->
            </div> <!-- .modal-content -->
            {!! Form::close() !!}
        </div> <!-- .modal-dialog -->
    </div> <!-- .modal -->
</div>
