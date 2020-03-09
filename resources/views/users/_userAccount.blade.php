@if(is_null(Session::get('previous_url')))
    {{ Session::put('previous_url', URL::previous()) }}
@endif

@if ($errors->any() && $from_page == 'account' && !$errors->has('delete_account_confirmation_email'))
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
    {!! Form::label('FieldUserSurname', trans('users.surname').':', ['class' => 'control-label col-sm-'.$label]) !!}
    <div class="col-sm-{!! $input !!}">
        {!! Form::text('lastname', null, ['class' => 'form-control','id' => 'FieldUserSurname', 'placeholder' => trans('users.surnameRequired')]) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('FieldUserΝame', trans('users.name').':', ['class' => 'control-label col-sm-'.$label]) !!}
    <div class="col-sm-{!! $input !!}">
        {!! Form::text('firstname', null, ['class' => 'form-control', 'id' => 'FieldUserΝame', 'placeholder' => trans('users.nameRequired')]) !!}
        <div class="help-block with-errors" style="margin:0px;"></div>
    </div>
</div>

@if($user->confirmed == 0)


    {{--Not Confirmed SECTION--}}



    @if($user->state == 'sso')


        {{--Not Confirmed SSO SECTION--}}

        @if(!empty(Session::get('emails')))

            <?php $emails = explode(';', Session::get('emails')); ?>

            @if(isset($invited_email_key))
                <input type="hidden" name="invited_email_key" value="{{$invited_email_key}}">
            @endif

            @foreach($emails as $k=>$email)
                @if($k==0)
                    <div class="form-group">
                        {!! Form::label('FieldUserPrimaryEmail', trans('users.primaryEmail'), ['class' => 'control-label col-sm-2 ']) !!}
                        <span class="glyphicon glyphicon-info-sign"
                              title="{!!  trans('users.primaryEmailMessage') !!}"></span>

                        <div class="col-sm-4">
                            {!! Form::text('email', $email,['id' => 'FieldUserEmail-'.$k, 'style' => 'width: 100%; background-color : #d1d1d1; ', 'aria-describedby' => 'helpBlockRole', 'readonly'=>'true'])!!}
                            <div class="help-block with-errors" style="margin:0;"></div>
                        </div>
                    </div>
                @else
                    <div class="form-group">
                        {!! Form::label('FieldUserExtraEmail-'.$k, trans('users.extraEmail').' '.$k, ['class' => 'control-label col-sm-2 ']) !!}
                        <span class="glyphicon glyphicon-info-sign"
                              title="{!!  trans('users.extraEmailMessage') !!}"></span>

                        <div class="col-sm-4">
                            {!! Form::text('extra_sso_email_'.$k, $email,['id' => 'FieldUserEmail-'.$k, 'style' => 'width: 100%; background-color : #d1d1d1;', 'aria-describedby' => 'helpBlockRole', 'readonly'=>'true'])!!}
                            <div class="help-block with-errors" style="margin:0px;"></div>
                        </div>
                    </div>
                @endif
            @endforeach
            @if(count($emails)<=3)
                <div class="form-group">
                    {!! Form::label('FieldUserExtraEmail-'.$k, trans('users.extraEmailsLeft').(4-count($emails)), ['class' => 'control-label col-sm-2 ']) !!}
                    <div class="col-sm-4">
                        {!!trans('users.userNotCreatedYet')!!}
                    </div>
                </div>
            @endif
        @else
            <div class="form-group">
                {!! Form::label('FieldUserEmail', 'Email:', ['class' => 'control-label col-sm-2 ']) !!}
                <div class="col-sm-4">
                    {!! Form::text('email', $user->email, ['class' => 'form-control', 'id' => 'FieldUserEmail', 'placeholder' => trans('users.emailRequired')]) !!}
                    <div class="alert alert-warning help-block"
                         role="alert">{!!trans('users.ExtraEmailsWarningPersonal')!!}</div>
                </div>
            </div>
        @endif
    @else

        {{--Not Confirmed Local SECTION--}}

        <div class="form-group">
            {!! Form::label('FieldUserEmail', 'Email:', ['class' => 'control-label col-sm-2 ']) !!}
            <div class="col-sm-4">
                {!! Form::text('email', $user->email, ['class' => 'form-control', 'id' => 'FieldUserEmail','readonly', 'placeholder' => trans('users.emailRequired')]) !!}
            </div>
        </div>

    @endif

@else

    {{--Confirmed SECTION--}}


    <div class="form-group">
        {!! Form::label('FieldUserEmail', 'Email:', ['class' => 'control-label col-sm-'.$label]) !!}
        <div class="col-sm-{!! $input !!} form-control-static">
            {{ $user->email }}

            @if($user->state == 'sso')
                <div class="alert alert-warning help-block"
                     role="alert">{!!trans('users.ExtraEmailsWarningPersonal')!!}</div>
            @endif
        </div>
    </div>

    {!! Form::hidden('email', $user->email) !!}

    <?php
    $extra_emails_sso = $user->extra_emails_sso()->toArray();
    $extra_emails_custom = $user->extra_emails_custom()->toArray();
    ?>


    @if($user->state=='sso')

        {{--Confirmed SSO SECTION--}}


        <div id="xtraMailsList">
            @foreach($extra_emails_sso as $mail)
                <div class="form-group" id="formGroup_{!! $mail['id'] !!}">
                    {!! Form::label('FieldExtraEmail'.$mail['id'], trans('users.extraEmail').':', ['class' => 'control-label col-sm-'.$label]) !!}
                    <div class="col-sm-{!! $input !!}">
                        {!! Form::text('extra_email_'.$mail['id'], $mail['email'], ['class' => 'form-control', 'id' => 'FieldExtraEmail'.$mail['id'], 'placeholder' => $mail['email'],'readonly'=>'true','style'=>'border-color:green;']) !!}
                        <div class="help-block with-errors" style="margin:0px;"></div>
                    </div>
                </div>
            @endforeach
            @foreach($extra_emails_custom as $mail)
                <div class="form-group" id="formGroup_{!! $mail['id'] !!}">
                    {!! Form::label('FieldExtraEmail'.$mail['id'], trans('users.extraEmail').':', ['class' => 'control-label col-sm-'.$label]) !!}
                    <div class="col-sm-{!! $input !!}">
                        @if($mail['confirmed']==1)
                            {!! Form::text('extra_email_'.$mail['id'], $mail['email'], ['class' => 'form-control', 'id' => 'FieldExtraEmail'.$mail['id'], 'placeholder' => $mail['email'],'readonly'=>'true','style'=>'border-color:green; margin-bottom:5px;']) !!}
                        @else
                            {!! Form::text('extra_email_'.$mail['id'], $mail['email'], ['class' => 'form-control', 'id' => 'FieldExtraEmail'.$mail['id'], 'placeholder' => $mail['email'],'readonly'=>'true','style'=>'border-color:red; margin-bottom:5px;']) !!}
                        @endif
                        <div id="editButtonsEmailSection_{!! $mail['id'] !!}">
                            <button type="button" class="btn btn-default" onclick="editExtraMail(this.id)"
                                    id="editCustomExtraMail_{!! $mail['id'] !!}"><span
                                        class="glyphicon glyphicon-pencil"></span></button>
                            <button type="button" class="btn btn-danger" onclick="deleteExtraMail(this.id)"
                                    id="deleteCustomExtraMail_{!! $mail['id'] !!}"><span
                                        class="glyphicon glyphicon-remove"></span></button>
                        </div>
                        <div id="editEmailSection_{!! $mail['id'] !!}" style="display:none;">

                            <button type="button" class="btn btn-default" onclick="saveExtraMail(this.id)"
                                    id="saveCustomExtraMail_{!! $mail['id'] !!}"><span
                                        class="glyphicon glyphicon-floppy-disk"></span></button>

                            <button type="button" class="btn btn-danger" onclick="cancelEditExtraMail(this.id)"
                                    id="cancelEditCustomExtraMail_{!! $mail['id'] !!}"><span
                                        class="glyphicon glyphicon-remove-circle"></span></button>
                        </div>

                        <div class="help-block with-errors" style="margin:0px;"></div>

                    </div>
                </div>
            @endforeach
        </div>


        <div class="form-group">
            {!! Form::label('FieldUserExtraEmail',trans('conferences.adduser').' '.trans('users.extraEmail'), ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-8">
                <div class="alert alert-danger" id="newAdditionalEmailErrors" style="display:none">
                </div>
                <div class="alert alert-success" id="newAdditionalEmailSuccess" style="display:none">
                    {!! trans('users.activationEmailSend') !!}
                </div>
                <div id="addnewCustomExtraMailArea"
                     style="@if(!((count($extra_emails_sso)+count($extra_emails_custom))<=2)) display:none; @endif">

                    <span id="plsWait" style="display:none;">Please wait...</span>
                    <input type="text" id="newAdditionalEmailField" class="form-control"
                           placeholder="{!! trans('conferences.adduser').' '.trans('users.extraEmail') !!}"/>
                    <button type="button" class="btn btn-primary" id='plusxtramailButton'
                            onclick="addExtraMail()"><span
                                class="glyphicon glyphicon-plus"></span></button>
                </div>
                <div class="alert alert-warning help-block" role="alert">
                    <p><strong>{!! trans('users.slotsavailable') !!}:</strong> <span
                                id="availableSlots">{!!(3-(count($extra_emails_sso)+count($extra_emails_custom)))!!}</span>
                    </p>
                </div>
            </div>
        </div>
    @else
        {{--Confirmed LOCAL SECTION--}}
        @foreach($extra_emails_sso as $key=>$mail)
            <div class="form-group" id="formGroup_{!! $mail['id'] !!}">
                {!! Form::label('FieldExtraEmail', trans('users.extraEmail').':', ['class' => 'control-label col-sm-'.$label]) !!}
                <div class="col-sm-{!! $input !!}">
                    {!! Form::text('extra_email', $mail['email'], ['class' => 'form-control', 'id' => 'FieldExtraEmail_sso_'.$key, 'placeholder' =>  trans('users.extraEmail'),'readonly'=>'true']) !!}
                    <div class="help-block with-errors" style="margin:0px;"></div>
                </div>
            </div>
        @endforeach
        @foreach($extra_emails_custom as $key=>$mail)
            <div class="form-group" id="formGroup_{!! $mail['id'] !!}">
                {!! Form::label('FieldExtraEmail', trans('users.extraEmail').':', ['class' => 'control-label col-sm-'.$label]) !!}
                <div class="col-sm-{!! $input !!}">
                    {!! Form::text('extra_email', $mail['email'], ['class' => 'form-control', 'id' => 'FieldExtraEmail_custom_'.$key, 'placeholder' =>  trans('users.extraEmail'),'readonly'=>'true']) !!}
                    <div class="help-block with-errors" style="margin:0px;"></div>
                </div>
            </div>
        @endforeach
    @endif
@endif



<div class="form-group">
    {!! Form::label('FieldUseStatus', trans('users.localUserShort').':', ['class' => 'control-label col-sm-'.$label]) !!}
    <div class="col-sm-{!! $input !!} form-control-static">
        {{ $user->state_string($user->state) }}
    </div>
</div>

@if(Auth::user()->hasRole('EndUser') == false)
    <div class="form-group">
        {!! Form::label('FieldUserPhone', trans('users.telephone').':', ['class' => 'control-label col-sm-'.$label]) !!}
        <div class="col-sm-{!! $input !!}">
            {!! Form::text('telephone', null, ['class' => 'form-control', 'id' => 'FieldUserPhone', 'placeholder' => trans('users.telephoneRequired')]) !!}
            <div class="help-block with-errors" style="margin:0px;"></div>
        </div>
    </div>
@else
    <div class="form-group">
        {!! Form::label('FieldUserPhone', trans('users.telephone').':', ['class' => 'control-label col-sm-'.$label]) !!}
        <div class="col-sm-{!! $input !!}">
            {!! Form::text('telephone', null, ['class' => 'form-control', 'id' => 'FieldUserPhone', 'placeholder' => trans('users.telephoneOptional')]) !!}
            <div class="help-block with-errors" style="margin:0px;"></div>
        </div>
    </div>
@endif

<div class="form-group">
    {!! Form::label('FieldUserImage', trans('users.uploadPhoto').':', ['class' => 'control-label col-sm-'.$label]) !!}
    <div class="col-sm-{!! $input !!} form-control-static">
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
    {!! Form::label('FieldUseRole', trans('users.role').':', ['class' => 'control-label col-sm-'.$label]) !!}
    <div class="col-sm-{!! $input !!} form-control-static" id="FieldUserRole">
        {{ trans($user->roles->first()->label) }}
    </div>
</div>


<!-- Institution -->
@if ($user->hasRole('EndUser') && $user->institutions->count() > 0 && $user->state == 'local')

    {{--Local End User With institutions section--}}

    @if($user->institutions->first()->slug == 'other')
        <div class="form-group">
            {!! Form::label('FieldUserOrg', trans('users.institution').':', ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-{!! $input !!}">
                {!! Form::select('institution_id', ['' => ''] + App\Institution::whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], 'other', ['id' => 'FieldUserOrg', 'style' => 'width: 100%'])!!}
            </div>
        </div>

        <div class="form-group" id="UserOrgNew">
            {!! Form::label('FieldUserOrgNew', trans('users.newInstitution').':', ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-{!! $input !!}">
                {!! Form::text('new_institution', $user->customValues()['institution'], ['class' => 'form-control', 'placeholder' => trans('users.enterInstitutionRequired'), 'id' => 'FieldUserOrgNew']) !!}
                <div class="help-block with-errors" style="margin:0px;"></div>
            </div>
        </div>

    @else
        <div class="form-group">
            {!! Form::label('FieldUserOrg', trans('users.institution').':', ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-{!! $input !!}">
                {!! Form::select('institution_id', ['' => ''] + App\Institution::whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], $user->institutions()->first()->id, ['id' => 'FieldUserOrg', 'style' => 'width: 100%'])!!}
            </div>
        </div>

        <div class="form-group" id="UserOrgNew">
            {!! Form::label('FieldUserOrgNew', trans('users.newInstitution').':', ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-{!! $input !!}">
                {!! Form::text('new_institution', null, ['class' => 'form-control', 'placeholder' => trans('users.enterInstitutionRequired'), 'id' => 'FieldUserOrgNew']) !!}
                <div class="help-block with-errors" style="margin:0px;"></div>
            </div>
        </div>

    @endif

@elseif(($user->state == 'sso' && $user->institutions->count() > 0) || $user->hasRole('EndUser') == false)

    {{--SSO NON End User With institutions section--}}

    {!! Form::hidden('institution_id', $user->institutions->first()->id) !!}

    @if($user->institutions->first()->slug == 'other')
        <div class="form-group">
            {!! Form::label('FieldUserOrg', trans('users.institution').':', ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-{!! $input !!} form-control-static">
                {{ $user->institutions->first()->title }} ({{ $user->customValues()['institution'] }})
            </div>
        </div>

    @else
        <div class="form-group">
            {!! Form::label('FieldUserOrg', trans('users.institution').':', ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-{!! $input !!} form-control-static">
                {{ $user->institutions->first()->title }}
            </div>
        </div>

    @endif

@else



    <div class="form-group">
        {!! Form::label('FieldUserOrg', trans('users.institution').':', ['class' => 'control-label col-sm-'.$label]) !!}
        <div class="col-sm-{!! $input !!}">
            {!! Form::select('institution_id', ['' => ''] + App\Institution::whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], null, ['id' => 'FieldUserOrg', 'style' => 'width: 100%'])!!}
        </div>
    </div>

    <div class="form-group" id="UserOrgNew">
        {!! Form::label('FieldUserOrgNew', trans('users.newInstitution').':', ['class' => 'control-label col-sm-'.$label]) !!}
        <div class="col-sm-{!! $input !!}">
            {!! Form::text('new_institution', null, ['class' => 'form-control', 'placeholder' => trans('users.enterInstitutionRequired'), 'id' => 'FieldUserOrgNew']) !!}
            <div class="help-block with-errors" style="margin:0px;"></div>
        </div>
    </div>

@endif


<!-- Department -->
@if ($user->hasRole('EndUser') && $user->departments->count() > 0)

    @if($user->departments->first()->slug == 'other')
        <div class="form-group" id="UserDepart">
            {!! Form::label('FieldUserDepart', trans('users.department').':', ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-{!! $input !!}">
                {!! Form::select('department_id', ['' => ''] + App\Department::where('institution_id', $user->institutions->first()->id)->whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], 'other', ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
            </div>
        </div>

        <div class="form-group" id="UserDepartNew">
            {!! Form::label('FieldUserDepartNew', trans('users.newDepartment').':', ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-{!! $input !!}">
                {!! Form::text('new_department', $user->customValues()['department'], ['class' => 'form-control', 'placeholder' => trans('users.enterDepartmentRequired'), 'id' => 'FieldUserDepartNew']) !!}
                <div class="help-block with-errors" style="margin:0px;"></div>
            </div>
        </div>

    @else
        <div class="form-group" id="UserDepart">
            {!! Form::label('FieldUserDepart', trans('users.department').':', ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-{!! $input !!}">
                {!! Form::select('department_id', ['' => ''] + App\Department::where('institution_id', $user->institutions->first()->id)->whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], $user->departments()->first()->id , ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
            </div>
        </div>

        <div class="form-group" id="UserDepartNew">
            {!! Form::label('FieldUserDepartNew', trans('users.newDepartment').':', ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-{!! $input !!}">
                {!! Form::text('new_department', null, ['class' => 'form-control', 'placeholder' => trans('users.enterDepartmentRequired'), 'id' => 'FieldUserDepartNew']) !!}
                <div class="help-block with-errors" style="margin:0px;"></div>
            </div>
        </div>
    @endif

@elseif ($user->hasRole('EndUser') && $user->departments->count() == 0 && $user->institutions->count() > 0)

    <div class="form-group" id="UserDepart">
        {!! Form::label('FieldUserDepart', trans('users.department').':', ['class' => 'control-label col-sm-'.$label]) !!}
        <div class="col-sm-{!! $input !!}">
            {!! Form::select('department_id', ['' => ''] + App\Department::where('institution_id', $user->institutions->first()->id)->whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], null , ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
        </div>
    </div>

    <div class="form-group" id="UserDepartNew">
        {!! Form::label('FieldUserDepartNew', trans('users.newDepartment').':', ['class' => 'control-label col-sm-'.$label]) !!}
        <div class="col-sm-{!! $input !!}">
            {!! Form::text('new_department', null, ['class' => 'form-control', 'placeholder' => trans('users.enterDepartmentRequired'), 'id' => 'FieldUserDepartNew']) !!}
            <div class="help-block with-errors" style="margin:0px;"></div>
        </div>
    </div>

@elseif($user->hasRole('DepartmentAdministrator') && $user->departments->count() > 0)

    @if($user->departments->first()->slug == 'other')

        {!! Form::hidden('department_id', 'other') !!}
        {!! Form::hidden('new_department', $user->customValues()['department']) !!}

        <div class="form-group">
            {!! Form::label('FieldUserDepart', trans('users.department').':', ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-{!! $input !!} form-control-static">
                {{ $user->departments->first()->title }} ({{ $user->customValues()['department'] }})
            </div>
        </div>

    @else
        {!! Form::hidden('department_id', $user->departments->first()->id) !!}

        <div class="form-group">
            {!! Form::label('FieldUserDepart', trans('users.department').':', ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-{!! $input !!} form-control-static">
                {{ $user->departments->first()->title }}
            </div>
        </div>

    @endif

@elseif ((($user->hasRole('SuperAdmin')) || $user->hasRole('InstitutionAdministrator')) && $user->institutions->count() > 0)

    {!! Form::hidden('department_id', $user->departments->first()->id) !!}

@else

    <div class="form-group" id="UserDepart">
        {!! Form::label('FieldUserDepart', trans('users.department').':', ['class' => 'control-label col-sm-'.$label]) !!}
        <div class="col-sm-{!! $input !!}">
            {!! Form::select('department_id', ['' => ''] , null, ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
        </div>
    </div>

    <div class="form-group" id="UserDepartNew">
        {!! Form::label('FieldUserDepartNew', trans('users.newDepartment').':', ['class' => 'control-label col-sm-'.$label]) !!}
        <div class="col-sm-{!! $input !!}">
            {!! Form::text('new_department', null, ['class' => 'form-control', 'placeholder' => trans('users.enterDepartmentRequired'), 'id' => 'FieldUserDepartNew']) !!}
            <div class="help-block with-errors" style="margin:0px;"></div>
        </div>
    </div>

@endif

@unless($user->state == 'sso')

    <div class="medium-gap"></div>

    <h4><i class="glyphicon glyphicon-lock"></i> {{trans('users.changePassword')}}</h4>
    <hr/>

    @if($page == 'account_activation' || $page == 'account' )
        <div class="form-group">
            {!! Form::label('FieldUserCurrentPassword','-', ['class' => 'control-label col-sm-'.$label]) !!}
            <p class="help-block col-sm-4">{{trans('users.info')}}</p>
        </div>

        <div class="form-group">
            {!! Form::label('FieldUserCurrentPassword', trans('users.currentPassword').':', ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-{!! $input !!}">
                {!! Form::password('current_password', ['class' => 'form-control','id' => 'FieldUserCurrentPassword', 'placeholder' => trans('users.currentPasswordOptional')]) !!}
                <div class="help-block with-errors" style="margin:0px;"></div>
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('FieldUserPassword', trans('users.newPassword').':', ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-{!! $input !!}">
                {!! Form::password('password', ['class' => 'form-control','id' => 'FieldUserPassword', 'placeholder' => trans('users.newPasswordOptional')]) !!}
                <div class="help-block with-errors" style="margin:0px;"></div>
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('FieldUserConfPassword', trans('users.confirmPassword').':', ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-{!! $input !!}">
                {!! Form::password('password_confirmation', ['class' => 'form-control','id' => 'FieldUserConfPassword', 'placeholder' => trans('users.confirmPasswordOptional')]) !!}
                <div class="help-block with-errors" style="margin:0px;"></div>
            </div>
        </div>

    @else
        <div class="form-group">
            {!! Form::label('FieldUserCurrentPassword', trans('users.currentPassword').':', ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-{!! $input !!}">
                {!! Form::password('current_password', ['class' => 'form-control','id' => 'FieldUserCurrentPassword', 'placeholder' => trans('users.currentPasswordRequired')]) !!}
                <div class="help-block with-errors" style="margin:0px;"></div>
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('FieldUserPassword', trans('users.newPassword').':', ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-{!! $input !!}">
                {!! Form::password('password', ['class' => 'form-control','id' => 'FieldUserPassword', 'placeholder' => trans('users.newPasswordRequired')]) !!}
                <div class="help-block with-errors" style="margin:0px;"></div>
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('FieldUserConfPassword', trans('users.confirmPassword').':', ['class' => 'control-label col-sm-'.$label]) !!}
            <div class="col-sm-{!! $input !!}">
                {!! Form::password('password_confirmation', ['class' => 'form-control','id' => 'FieldUserConfPassword', 'placeholder' => trans('users.confirmPasswordRequired')]) !!}
                <div class="help-block with-errors" style="margin:0px;"></div>
            </div>
        </div>
    @endif



@endunless

@if ($user->institutions->count() > 0 && $user->departments->count() > 0)
    {!! Form::hidden('institution_id_current', $user->institutions->first()->id) !!}
    {!! Form::hidden('department_id_current', $user->departments->first()->id) !!}

@elseif ($user->institutions->count() > 0 && $user->departments->count() == 0)
    {!! Form::hidden('institution_id_current', $user->institutions->first()->id) !!}
    {!! Form::hidden('department_id_current', null) !!}
@else
    {!! Form::hidden('institution_id_current', null) !!}
    {!! Form::hidden('department_id_current', null) !!}
@endif
{!! Form::hidden('current_role', $user->roles->first()->name) !!}
{!! Form::hidden('role', $user->roles->first()->name) !!}
{!! Form::hidden('from_page', $from_page) !!}

@if($page == 'edit' || $page == 'account_activation')

    <div class="row">
        <div class="col-sm-12">
									<span class="pull-right">
										<div class="btn-group" role="group" id="TeleInitialSaveGroupButtons">
                                            {!! Form::submit($submitBtn, ['class' => 'btn btn-primary', 'name' => 'add_details']) !!}
                                        </div>
										<a href="{{ Session::get('previous_url') }}">
                                            <button type="button" class="btn btn-default"
                                                    id="TeleReturn">{{trans('users.return')}}</button>
                                        </a>
									</span>
        </div>
    </div>
@endif

{!! Form::hidden('status', 1) !!}
{!! Form::hidden('confirmed', 1) !!}

@if(str_contains( Request::path(), 'edit'))
    <div class="row">
        <div class="col-sm-12">
								<span class="pull-right">
									<div class="btn-group" role="group" id="TeleInitialSaveGroupButtons">
                                        {!! Form::submit($submitBtn, ['class' => 'btn btn-primary', 'name' => 'add_details']) !!}
                                    </div>
									<a href="{{ Session::get('previous_url') }}">
                                        <button type="button" class="btn btn-default"
                                                id="TeleReturn">{{trans('users.return')}}</button>
                                    </a>
								</span>
        </div>
    </div>
@endif
@if($user->state == 'sso' && $user->confirmed==1)
    <script type="text/javascript">
        var tmp = new Array();

        var institutionId = $("InstitutionId").val();
        var availableMailSlots = {!!(3-(count($extra_emails_sso)+count($extra_emails_custom)))!!};

        function addExtraMail() {
            var input = $("#newAdditionalEmailField").val();
            var valid = validateEmail(input);


            if (input && valid == true) {
                $("#plusxtramailButton").attr('disabled', true);
                $("#plsWait").show();
                $("#newAdditionalEmailField").attr('readonly', true);
                $.post("addExtraEmail", {
                    _token: "{!!csrf_token()!!}",
                    email: $("#newAdditionalEmailField").val()
                })
                    .done(function (data) {
                        console.log(data);
                        var response = JSON.parse(data);
                        $("#plsWait").hide();
                        $("#plusxtramailButton").attr('disabled', false);
                        if (response.status === 'success') {
                            $("#newAdditionalEmailField").attr('readonly', false);
                            $("#newAdditionalEmailErrors").hide();
                            $("#newAdditionalEmailSuccess").show();
                            availableMailSlots--;
                            $("#availableSlots").html(availableMailSlots);
                            $("#xtraMailsList").append('<div class="form-group" id="formGroup_' + response.message + '">' +
                                '<label for="FieldExtraEmail' + response.message + '" class="control-label col-sm-4">{!!trans('users.extraEmail')!!}:</label>' +
                                '<div class="col-sm-8"><input class="form-control" id="FieldExtraEmail' + response.message + '" placeholder="' + $("#newAdditionalEmailField").val() + '" name="extra_email_' + response.message + '" value="' + $("#newAdditionalEmailField").val() + '" type="text" style="border-color: #a94442; margin-bottom:5px;" readonly>' +
                                '<div id="editButtonsEmailSection_' + response.message + '">' +
                                '<button type="button" class="btn btn-default" onclick="editExtraMail(this.id)" id="editCustomExtraMail_' + response.data + '"><span class="glyphicon glyphicon-pencil" ></span></button>' +
                                '<button type="button" class="btn btn-danger" id="deleteCustomExtraMail_' + response.message + '" onclick="deleteExtraMail(this.id)"><span class="glyphicon glyphicon-remove"></span></button></div>' +
                                '<div id="editEmailSection_' + response.message + '" style="display:none;">' +
                                '<button type="button" class="btn btn-default" onclick="saveExtraMail(this.id)" id="saveCustomExtraMail_' + response.data + '">' +
                                '<span class="glyphicon glyphicon-floppy-disk"></span></button>' +
                                '<button type="button" class="btn btn-danger" onclick="cancelEditExtraMail(this.id)" id="cancelEditCustomExtraMail_' + response.data + '">' +
                                '<span class="glyphicon glyphicon-remove-circle"></span></button> </div>' +
                                '<div class="help-block with-errors" style="margin:0px;"></div></div>');

                            $("#newAdditionalEmailField").val('');
                            if (availableMailSlots === 0) {
                                $("#addnewCustomExtraMailArea").hide();
                            }
                            setTimeout(function () {
                                $("#newAdditionalEmailSuccess").hide();
                            }, 8000);
                        }
                        else if (response.status === 'error' && response.message === 'email_used') {
                            $("#newAdditionalEmailField").attr('readonly', false);
                            $("#newAdditionalEmailErrors").show();
                            $("#newAdditionalEmailErrors").html('{!!trans('requests.emailNotUnique')!!}');
                        }
                        else if (response.status === 'error' && response.message === 'email_used_pending') {
                            $("#newAdditionalEmailField").attr('readonly', false);
                            $("#newAdditionalEmailErrors").show();
                            $("#newAdditionalEmailErrors").html('{!!trans('requests.emailNotUniquePending')!!}');
                        }

                        else if (response.status === 'error' && response.message === 'email_own_used') {
                            $("#newAdditionalEmailField").attr('readonly', false);
                            $("#newAdditionalEmailErrors").show();
                            $("#newAdditionalEmailErrors").html('{!!trans('requests.emailOwnUsed')!!}');
                        }
                    });

            }
            else if (!input || valid == false) {
                $("#newAdditionalEmailErrors").show();
                $("#newAdditionalEmailErrors").html('{!!trans('users.notValidEmail')!!}');
                $("#newAdditionalEmailField").focus();
                setTimeout(function () {
                    $("#newAdditionalEmailErrors").hide();
                }, 5000);

            }
        }

        function deleteExtraMail(id) {
            var r = confirm("Are you sure you want to delete this email?");
            if (r == true) {
                var emailID = id.split('_')[1];
                $.post("deleteExtraEmail", {
                    _token: "{!!csrf_token()!!}",
                    id: parseInt(emailID)
                })
                    .done(function (data) {
                        console.log(data);
                        var response = JSON.parse(data);
                        if (response.status == 'success') {
                            $("#formGroup_" + emailID).remove();
                            availableMailSlots++;
                            $("#availableSlots").html(availableMailSlots);
                            $("#addnewCustomExtraMailArea").show();
                        }
                    })
                    .fail(function (error) {
                        console.log(error.responseText);
                    })
            } else {
                console.log("You pressed Cancel!");
            }
        }

        function editExtraMail(id) {

            var emailID = id.split('_')[1];

            console.log(emailID);

            var ele = $("#FieldExtraEmail" + emailID);
            tmp[emailID] = ele.val();

            ele.attr('readonly', false);
            ele.attr('disabled', false);
            ele.focus();
            $("#editEmailSection_" + emailID).show();
            $("#editButtonsEmailSection_" + emailID).hide();
        }


        function cancelEditExtraMail(id) {
            var emailID = id.split('_')[1];
            console.log(emailID);
            var ele = $("#FieldExtraEmail" + emailID);
            ele.val(tmp[id]);
            tmp[id] = null;
            ele.attr('readonly', true);

            $("#editEmailSection_" + emailID).hide();
            $("#editButtonsEmailSection_" + emailID).show();
        }


        function saveExtraMail(id) {
            $("#plsWait").show();
            var emailID = id.split('_')[1];
            var ele = $("#FieldExtraEmail" + emailID);
            var oldValue = tmp[emailID];
            console.log('Old Value:' + oldValue);
            var input = ele.val().split('@')[1];
            if (input && ele.val() !== oldValue) {
                $.post("updateExtraEmail", {
                    _token: "{!!csrf_token()!!}",
                    id: parseInt(emailID),
                    value: ele.val()
                })
                    .done(function (data) {
                        var response = JSON.parse(data);
                        if (response.status === 'success') {
                            $("#editEmailSection_" + emailID).hide();
                            $("#editButtonsEmailSection_" + emailID).show();
                            $("#plsWait").hide();
                            ele.attr('readonly', true);
                            ele.css('border-color', 'red');
                            $("#newAdditionalEmailSuccess").show();
                            setTimeout(function () {
                                $("#newAdditionalEmailSuccess").hide();
                            }, 5000);
                        }
                        console.log(data);
                    })
                    .fail(function (error) {
                        console.log(error.responseText);
                    })
            }
            {{--else if (input) {--}}
                    {{--$("#newAdditionalEmailErrors").show();--}}
                    {{--$("#newAdditionalEmailErrors").html('{!!trans('users.notInDomains')!!}');--}}
                    {{--ele.focus();--}}
                    {{--$("#plsWait").hide();--}}
                    {{--$("#editEmailSection_" + emailID).hide();--}}
                    {{--$("#editButtonsEmailSection_" + emailID).show();--}}
                    {{--}--}}
            else if (!input) {
                $("#newAdditionalEmailErrors").show();
                $("#newAdditionalEmailErrors").html('{!!trans('users.notValidEmail')!!}');
                ele.focus();
                $("#plsWait").hide();
            }
            else if (ele.val() === oldValue) {
                console.log('Same value do nothing');
                $("#plsWait").hide();
                $("#editEmailSection_" + emailID).hide();
                $("#editButtonsEmailSection_" + emailID).show();
            }

        }

        function validateEmail(email) {
            var re = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
            return re.test(email);
        }

    </script>
@endif





