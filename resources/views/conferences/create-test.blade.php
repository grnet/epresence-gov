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


    <link rel='stylesheet' type='text/css' href='/timepicki/css/timepicki.css'/>

    <script type='text/javascript' src='/timepicki/js/timepicki.js'></script>

    <link href="/css/main.css" rel="stylesheet">
    <link href="/css/eDatatables.css" rel="stylesheet">



    <script type="text/javascript" src="/datatables/jquery.dataTables.js"></script>
    <script type="text/javascript" src="/datatables/dataTables.bootstrap.js"></script>
    <script type="text/javascript" src="/datatables/date-eu.js"></script>

    <link href="/datatables/dataTables.bootstrap.css" rel="stylesheet">


    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->

    <script type="text/javascript">
        $(document).ready(function () {

// ΕΝΕΡΓΟΠΟΙΗΣΗ TOOLTIP

            $('[data-toggle="tooltip"]').tooltip();


// ΛΕΠΤΟΜΕΡΕΙΕΣ ΤΗΛΕΔΙΑΣΚΕΨΗΣ

            $('.summernote').summernote({
                lang: 'el-GR',
                height: 50,
                toolbar: [
                    // [groupName, [list of button]]
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['table', ['table']],
                    ['insert', ['link', 'hr']],
                ]
            });

            $("#TeleDetailsAlert").hide();
            $("#TeleSaveDetails").hide();
            $("#ParticipatsTitle").hide();
            $("#ParticipatsBody").hide();
            $("#ParticipatsBody").hide();
            $("#ExitFromPageDiv").hide();


            $("#TeleSave").click(function () {
                // ελέγχους και αν όλα καλά..
                $("#TeleDetailsAlert").show();
                setTimeout(function () {
                    window.location.href = "/conferences";
                }, 800);
            });

            $("#TeleSaveDetails").click(function () {
                // ελέγχους και αν όλα καλά..
                $("#TeleDetailsAlert").show();
                setTimeout(function () {
                    $("#TeleDetailsAlert").hide();
                }, 700);
            });

            $("#TeleSaveAndAddUsers").click(function () {
                // ελέγχους και αν όλα καλά..
                $("#TeleDetailsAlert").show();
                setTimeout(function () {
                    $("#TeleDetailsAlert").hide();
                    $("#TeleInitialSaveGroupButtons").hide();
                    $("#TeleReturn").hide();
                    $("#TeleSaveDetails").show();
                    $("#TeleTile").text("{{trans('conferences.conferenceDetails')}}");
                    $("#ParticipatsTitle").show();
                    $("#ParticipatsBody").show();
                    $("#ExitFromPageDiv").show();
                }, 800);
            });

            $("#TeleReturn, #ExitFromPage").click(function () {
                window.location.href = "/conferences";
            });

            $("#GotoTop").click(function () {
                var gotopage = $("#ParticipatsTitle").offset().top;
                $("html, body").animate({scrollTop: gotopage});
            });



            $('#FieldStartTime').timepicki({
                show_meridian: false,
                min_hour_value: 0,
                max_hour_value: 23,
                step_size_minutes: 15,
                overflow_minutes: true,
                increase_direction: 'up',
                disable_keyboard_mobile: true,
                start_time: ["{{$default_values['start_hour']}}", "{{$default_values['start_minute']}}"]
            });


            $('#FieldEndTime').timepicki({
                show_meridian: false,
                min_hour_value: 0,
                max_hour_value: 23,
                step_size_minutes: 15,
                overflow_minutes: true,
                increase_direction: 'up',
                disable_keyboard_mobile: true,
                start_time: ["{{$default_values['end_hour']}}", "{{$default_values['end_minute']}}"]
            });



            $('#FieldStartDate').datepicker({
                format: "dd-mm-yyyy",
                todayBtn: "linked",
                language: "el",
                autoclose: true,
                todayHighlight: true
            });
            $('#FieldEndDate').datepicker({
                format: "dd-mm-yyyy",
                todayBtn: "linked",
                language: "el",
                autoclose: true,
                todayHighlight: true
            });

            $('#FieldStartDate').datepicker().on('changeDate', function(e) {
                startDate = e.date;
                $('#FieldEndDate').datepicker('setDate',e.date);
            });

            $('#FieldEndDate').datepicker().on('changeDate', function(e) {

                if(e.date>=startDate)
                    endDate = e.date;
                else
                    $('#FieldEndDate').datepicker('setDate',startDate);

            });

            startDate =  $('#FieldStartDate').datepicker('getDate');
            endDate = $('#FieldEndDate').datepicker('getDate');

            var ShoursStart = {{$default_values['start_hour']}};
            var SminutesStart = {{$default_values['start_minute']}};

            var startTime = new Date(startDate.getFullYear(),startDate.getMonth(),startDate.getDate(),ShoursStart,SminutesStart,0,0);


            var EhoursStart = {{$default_values['end_hour']}};
            var EminutesStart = {{$default_values['end_minute']}};

            var endTime = new Date(endDate.getFullYear(),endDate.getMonth(),endDate.getDate(),EhoursStart,EminutesStart,0,0);


            diff = Math.abs(endTime - startTime);

        });
    </script>
@endsection
@section('extra-css')
    <style>
        .container {
            min-width: 550px !important;
        }

        .zero-width {
            display: none;
            width: 0;
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
        /* CLASSES FOR USERS DATATABLE END */
    </style>
@endsection
@section('conference-active')
    class = "active"
@endsection
@section('content')
    <section id="vroom">
        <div class="container">
            <!-- Form Details -START -->
            <div class="box" style="padding:0px; background-color:transparent; margin-top:100px">
                <h4 id="TeleTile">{{trans('conferences.testConference')}}</h4>
                <p>{!! trans('conferences.test_conference_create_info') !!}</p>
            </div>
            <div class="box" style="padding:30px 30px  20px 30px">
                <div class="row" style="margin:0px;">
                    @if ($errors->any())
                        <ul class="alert alert-danger" style="margin: 0px 15px 10px 15px">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                    {!! Form::open(array('url' => 'test-conferences', 'method' => 'post', 'class' => 'form-horizontal', 'id' => 'OrgForm', 'role' => 'form')) !!}
                    @include('conferences._testForm', ['title'=>$default_values['title'],'start_date' => $default_values["start_date"], 'start_time' => $default_values["start_time"], 'end_date' => $default_values["end_date"], 'end_time' => $default_values["end_time"],'invisible' => 0, 'submitBtn' => trans('conferences.saveContinue'), 'copyBtn' => '','apella_id'=>''])
                    {!! Form::close() !!}
                </div>
            </div>
            <!--/.box-->
            <!-- Form Details -END -->
        </div>
        <!--/.container-->
    </section>
@endsection
