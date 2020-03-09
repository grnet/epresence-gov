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
                <h4>{{trans('site.activationForUser')}}: {{ $user->email }}</h4>
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

                @include('account_activation.'.$user->state)
            </div>
        </div><!--/.box-->
    </section>
    <!-- Form Details -END -->
@endsection
