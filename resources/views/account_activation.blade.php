@extends('app')

@section('header-javascript')
    <link href="/select2/select2.css" rel="stylesheet">
    <script type="text/javascript" src="/select2/select2.js"></script>
    <script type="text/javascript" src="/select2/select2_locale_el.js"></script>
    <link rel="stylesheet" href="/select2/select2-small.css">

    <!-- checkbox -->
    <script src="/bootstrap-checkbox-x/checkbox-x.js" type="text/javascript"></script>
    <link rel="stylesheet" href="/bootstrap-checkbox-x/checkbox-x.css">

    <!-- bootstrap text editor       -->
    <link href="/summernote/summernote.css" rel="stylesheet">
    <script src="/summernote/summernote.min.js"></script>
    <script src="/summernote/summernote-el-GR.js"></script>

    <link rel="stylesheet" href="/css/font-awesome.css">

    <!-- bootstrap date-picker    -->
    <script type="text/javascript" src="/bootstrap-datepicker-master/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="/bootstrap-datepicker-master/bootstrap-datepicker.el.js"></script>
    <link href="/bootstrap-datepicker-master/datepicker3.css" rel="stylesheet">

    <!-- bootstrap clock-picker    -->
    <script type="text/javascript" src="/clock-picker/clockpicker.js"></script>
    <link href="/clock-picker/clockpicker.css" rel="stylesheet">


    <link href="/css/main.css" rel="stylesheet">
    <link href="/css/eDatatables.css" rel="stylesheet">

    <script type="text/javascript" src="/datatables/jquery.dataTables.js"></script>
    <script type="text/javascript" src="/datatables/dataTables.bootstrap.js"></script>
    <script type="text/javascript" src="/datatables/date-eu.js"></script>

    <link href="/datatables/dataTables.bootstrap.css" rel="stylesheet">

    <script type="text/javascript">
        $(document).ready(function () {
            var new_org_container = $("#NewOrgContainer");
            var new_dep_container = $("#NewDepContainer");
            var dep_container = $("#DepContainer");
            var department_select_field = $("#FieldUserDepart");
            var new_custom_department_field = $("#FieldUserDepartNew");
            var organization_select_field = $("#FieldUserOrg");

            $('[data-toggle="tooltip"]').tooltip();

            @if($user->state == 'local')
            organization_select_field.select2({placeholder: "{!!trans('site.selectInstitution')!!}"});

            // FieldUserOrg On change
            organization_select_field.change(function () {

                if (organization_select_field.val() === "other") {

                    department_select_field.select2().load("/institutions/departments/other", function () {
                        department_select_field.val("other").trigger("change");
                    });

                    new_org_container.show();
                    dep_container.hide();
                    new_dep_container.show();
                    new_custom_department_field.show();


                } else if (organization_select_field.val() !== "other") {
                    new_org_container.hide();
                    new_dep_container.hide();
                    dep_container.show();

                    if (organization_select_field.val() !== ""){
                        department_select_field.select2().load("/institutions/departments/" + organization_select_field.val(), function () {
                            department_select_field.select2("val", "").trigger("change");
                        });
                     }
                }

            }).trigger("change");
            @endif

            @if($user->state=="local" && !$institution)
            department_select_field.select2({placeholder: "{!!trans('site.selectInstitutionFirst')!!}"});
            @else
            department_select_field.select2({placeholder: "{!!trans('site.selectDepartment')!!}"});
            @endif

            // UserDepart On change
            department_select_field.on("change", function () {

                if (department_select_field.val() === "other") {
                    new_dep_container.show();
                    new_custom_department_field.show();
                } else if (department_select_field.val() !== "other" && department_select_field.val() > 0) {
                    new_dep_container.hide();
                    new_custom_department_field.hide();
                }
                else if (department_select_field.val() === "") {
                    new_dep_container.hide();
                    new_custom_department_field.hide();
                }
            }).trigger("change");
        });
    </script>
@endsection
@section('content')
    <section id="Users">
        <div class="container">
            <div class="box first" style="margin-top:100px;">
                <h4>{{trans('site.activationForUser')}}: {{ $user->firstname }} {{ $user->lastname }}</h4>
                <hr/>
                <div class="alert alert-warning">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    {{trans('site.activationText')}}
                </div>
                @if ($errors->any() && !$errors->has('delete_account_confirmation_email'))
                    <div class="alert alert-danger">
                        <ul>
                            <strong>{{trans('users.changesNotSaved')}}</strong>
                            @foreach($errors->all() as $error)
                                <li>{!! $error !!}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if(session('message'))
                    <div class="alert alert-info">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        {{ session('message') }}
                    </div>
                @endif
                {!! Form::model($user, array('url' => ['/account_activation'], 'method' => 'POST', 'class' => 'form-horizontal', 'id' => 'OrgForm', 'role' => 'form', 'files' => true)) !!}
                <div class="form-group">
                    {!! Form::label('FieldUserSurname', trans('users.surname').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        {!! Form::text('lastname', $user->lastname, ['disabled'=>true,'class' => 'form-control','id' => 'FieldUserSurname', 'placeholder' => trans('users.surnameRequired')]) !!}
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('FieldUserΝame', trans('users.name').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        {!! Form::text('firstname', $user->firstname, ['disabled'=>true,'class' => 'form-control', 'id' => 'FieldUserΝame', 'placeholder' => trans('users.nameRequired')]) !!}
                        <div class="help-block with-errors" style="margin:0;"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="emailInput" class="control-label col-sm-4">{{trans('users.primaryEmail')}} <span class="glyphicon glyphicon-info-sign" title="{!!  trans('users.primaryEmailMessage') !!}"></span></label>
                    <div class="col-sm-8">
                        {!! Form::text('email', $user->email,['class' => 'form-control','aria-describedby' => 'helpBlockRole','id'=>'emailInput','placeholder'=>trans('users.primaryEmail')])!!}
                        @if(empty($user->email))
                            <div class="help-block with-errors" style="margin:0;">Μόλις συμπληρώσετε το παραπάνω πεδίο με την δεύθυνση σας και πατήσετε "Αποστολή email επιβεβαίωσης" θα σας αποσταλεί email επιβεβαίωσης, για να προχωρήσετε θα πρέπει να πατήστε τον σύνδεσμο στο email επιβεβαίωσης</div>
                        @else
                            <div class="help-block with-errors" style="margin:0;">Σας έχει αποστάλει email επιβεβαίωσης στην παραπάνω διεύθυνση, για να προχωρήσετε παρακαλώ πατήστε τον σύνδεσμο στο email επιβεβαίωσης</div>
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('FieldUserPhone', trans('users.telephone').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        {!! Form::text('telephone',$user->telephone, ['class' => 'form-control', 'id' => 'FieldUserPhone', 'placeholder' => trans('users.telephoneOptional')]) !!}
                        <div class="help-block with-errors" style="margin:0;"></div>
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
                        <p class="help-block">{{trans('users.acceptedFileTypes')}}: jpeg, png, bmp, gif, svg. {{trans('users.maxFileSize')}}: 300kB.</p>
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
                </div>
                {{--Institution section end--}}
                {{--Department section start--}}
                <div class="form-group" id="DepContainer">
                    {!! Form::label('FieldUserDepart', trans('users.department').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                         {!! Form::select('department_id',App\Department::where('institution_id', $institution->id)->orderBy('title')->pluck('title', 'id')->toArray(),$department->id , ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
                    </div>
                </div>

                <div class="form-group">
                    <label for="Terms" class="control-label col-sm-4">{{trans('site.termsAcceptance')}} : </label>
                    <div class="col-sm-8 form-control-static">
                        <input name="accept_terms_input" type="checkbox" @if(!empty($user->accepted_terms) || (old('accept_terms_input') =='on')) checked @endif>
                        <a href="/terms" target="_blank"> {{trans('site.termsSite')}}</a>
                    </div>
                </div>
                <div class="form-group">
                    <label for="Privacy" class="control-label col-sm-4">{{trans('site.privacyPolicyAcceptance')}} : </label>
                    <div class="col-sm-8 form-control-static">
                        <input name="privacy_policy_input" type="checkbox" @if(!empty($user->accepted_terms) || (old('privacy_policy_input') =='on')) checked @endif>
                        <a href="/privacy_policy" target="_blank"> {{trans('site.privacy_policy')}}</a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
		<span class="pull-right">
				<div class="btn-group" role="group" id="TeleInitialSaveGroupButtons">
                      {!! Form::submit('Αποστολή email επιβεβαίωσης', ['class' => 'btn btn-primary']) !!}
                </div>
		</span>
                    </div>
                </div>
                {!! Form::close() !!}            </div>
        </div><!--/.box-->
    </section>
    <!-- Form Details -END -->
@endsection
