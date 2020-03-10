@if(is_null(Session::get('previous_url')))
    {{ Session::put('previous_url', URL::previous()) }}
@endif

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

@endsection
@section('extra-css')
    <style>
        .container {
            min-width: 550px !important;
        }

        .zero-width {
            display: none;
            width: 0px;
        }

        table#example td {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            -o-text-overflow: ellipsis !important;
            width: 10px;
            min-width: 10px;
            max-width: 10px;
        }


        .cellPName {
            width: 300px !important;
        }

        .cellPRole {
            width: 120px !important;
        }

        .cellPEmail {
            width: 210px !important;
        }

        .cellPType {
            width: 100px !important;
        }

        .cellPStatus {
            width: 110px !important;
        }

        .cellPSendEmail {
            width: 85px !important;
        }

        .cellPConfirm {
            width: 85px !important;
        }

        .cellPButton {
            padding: 3px !important;
            width: 50px !important;
            min-width: 50px !important;
            max-width: 50px !important;
        }

        tfoot {
            display: table-header-group;
        }

        .datepicker {
            padding: 0px;
        }

        /* CLASSES FOR USERS DATATABLE START */
        table#UsersExample td {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            -o-text-overflow: ellipsis !important;
            width: 10px;
            min-width: 10px;
            max-width: 10px;
        }

        .cellName {
            width: 255px !important;
        }

        .cellRole {
            width: 80px !important;
        }

        .cellEmail {
            width: 125px !important;
        }

        .cellOrg {
            width: 170px !important;
        }

        .cellDepart {
            width: 120px !important;
        }

        .cellType {
            width: 70px !important;
        }

        .cellStatus {
            width: 80px !important;
        }

        .cellButton {
            padding: 3px !important;
            width: 50px !important;
            min-width: 50px !important;
            max-width: 50px !important;
        }

        /* CLASSES FOR USERS DATATABLE END */
    </style>
@endsection

@section('institutions-active')
    class = "active"
@endsection

@section('content')
    <section id="Users">
        <div class="container">
            <div class="box first" style="margin-top:100px">
                <div class="small-gap"></div>
                <h3>{{trans('deptinst.editInstitution')}}</h3>
                <div class="small-gap"></div>
                @if ($errors->any())
                    <ul class="alert alert-danger" style="margin: 0px 15px 10px 15px">
                        <strong>{{trans('deptinst.changesNotSaved')}}</strong>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
                {!! Form::model($institution, array('url' => ['institutions/' . $institution->id], 'method' => 'PATCH', 'class' => 'form-horizontal', 'id' => 'OrgForm', 'role' => 'form')) !!}
                <div class="form-group">
                    {!! Form::label('title', trans('deptinst.description').':', ['class' => 'control-label col-sm-2 ']) !!}
                    <div class="col-sm-4">
                        {!! Form::text('title', null, ['class' => 'form-control', 'placeholder' => trans('deptinst.institutionDescriptionRequired'), 'data-error' => trans('deptinst.required')]) !!}
                        <div class="help-block with-errors" style="margin:0px;"></div>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('category', 'Category:', ['class' => 'control-label col-sm-2 ']) !!}
                    <div class="col-sm-4">
                        {!! Form::text('category', null, ['class' => 'form-control', 'placeholder' => 'Category', 'type' => 'text']) !!}
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('type', 'Type:', ['class' => 'control-label col-sm-2 ']) !!}
                    <div class="col-sm-4">
                        {!! Form::text('type', null, ['class' => 'form-control', 'placeholder' => 'Type', 'type' => 'text']) !!}
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('ws_id', 'Webservice Id:', ['class' => 'control-label col-sm-2 ']) !!}
                    <div class="col-sm-4">
                        {!! Form::text('ws_id', null, ['class' => 'form-control', 'placeholder' => 'Webservice Id', 'type' => 'text']) !!}
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('api_code', 'Api Code:', ['class' => 'control-label col-sm-2 ']) !!}
                    <div class="col-sm-4">
                        {!! Form::text('api_code', null, ['class' => 'form-control', 'placeholder' => 'Api Code', 'type' => 'text']) !!}
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
									<span class="pull-right">
										<div class="btn-group" role="group" id="TeleInitialSaveGroupButtons">
											{!! Form::submit(trans('deptinst.saveChanges'), ['class' => 'btn btn-primary', 'name' => 'add_details']) !!}
										</div>
										<a href="{{ Session::get('previous_url') }}"><button type="button"
                                                                                             class="btn btn-default"
                                                                                             id="TeleReturn">{{trans('deptinst.return')}}</button></a>
									</span>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div><!--/.box-->
        <!-- Form Details -END -->
@endsection
