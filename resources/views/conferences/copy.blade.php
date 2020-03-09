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
	
	<link rel='stylesheet' type='text/css'href='/timepicki/css/timepicki.css'/>
    <script type='text/javascript'src='/timepicki/js/jquery.js'></script>
	<script type='text/javascript'src='/timepicki/js/timepicki.js'></script>
        
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
		$(document).ready(function() {		
			  		
// ΕΝΕΡΓΟΠΟΙΗΣΗ TOOLTIP

  		$('[data-toggle="tooltip"]').tooltip();


// ΛΕΠΤΟΜΕΡΕΙΕΣ ΤΗΛΕΔΙΑΣΚΕΨΗΣ

	$('.summernote').summernote({
		lang: 'el-GR',
		height: 50
	});

	$("#TeleDetailsAlert").hide();
	$("#TeleSaveDetails").hide();
	$("#ParticipatsTitle").hide();
	$("#ParticipatsBody").hide();
	$("#ParticipatsBody").hide();
	$("#ExitFromPageDiv").hide();
	


	$('.clockpicker').clockpicker();

	$('.datepicker').datepicker({
		format: "dd-mm-yyyy",
		todayBtn: "linked",
		language: "el",
		autoclose: true,
		todayHighlight: true
    });


		
	$("#TeleSave").click(function() {
		// ελέγχους και αν όλα καλά..
		$("#TeleDetailsAlert").show();
		setTimeout(function() { window.location.href = "/conferences"; }, 800);
	});	
	
	$("#TeleSaveDetails").click(function() {
		// ελέγχους και αν όλα καλά..
		$("#TeleDetailsAlert").show();
		setTimeout(function() { $("#TeleDetailsAlert").hide(); }, 700);				
	});

	$("#TeleSaveAndAddUsers").click(function() {
		// ελέγχους και αν όλα καλά..
		$("#TeleDetailsAlert").show();
		setTimeout(function() { $("#TeleDetailsAlert").hide();
		$("#TeleInitialSaveGroupButtons").hide();
		$("#TeleReturn").hide();		
		$("#TeleSaveDetails").show();
		$("#TeleTile").text("{{trans('conferences.conferenceDetails')}}");	
		$("#ParticipatsTitle").show();
		$("#ParticipatsBody").show();
		$("#ExitFromPageDiv").show();
		}, 800);
	});	

	$("#TeleReturn, #ExitFromPage").click(function() {
		window.location.href = "/conferences";	
	});		
		
	$("#GotoTop").click(function() {
		var gotopage=$("#ParticipatsTitle").offset().top;
		$("html, body").animate({ scrollTop: gotopage});
	});

            $('#FieldStartTime').timepicki({
                show_meridian: false,
                min_hour_value: 0,
                max_hour_value: 23,
                step_size_minutes: 15,
                overflow_minutes: true,
                increase_direction: 'up',
                disable_keyboard_mobile: true,
                start_time: ["<?php echo (string)strstr($conference->getTime($conference->start), ':', true); ?>", "<?php echo (string)substr($conference->getTime($conference->start), 3); ?>"]
            });

            $('#FieldEndTime').timepicki({
                show_meridian: false,
                min_hour_value: 0,
                max_hour_value: 23,
                step_size_minutes: 15,
                overflow_minutes: true,
                increase_direction: 'up',
                disable_keyboard_mobile: true,
                start_time: ["<?php echo (string)strstr($conference->getTime($conference->end), ':', true); ?>", "<?php echo (string)substr($conference->getTime($conference->end), 3); ?>"]
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

            var ShoursStart = {{(string)strstr($conference->getTime($conference->start), ':', true)}};
            var SminutesStart = {{(string)substr($conference->getTime($conference->start), 3)}};

            var startTime = new Date(startDate.getFullYear(),startDate.getMonth(),startDate.getDate(), ShoursStart, SminutesStart, 0, 0);


            var EhoursStart = {{(string)strstr($conference->getTime($conference->end), ':', true)}};
            var EminutesStart = {{(string)substr($conference->getTime($conference->end), 3)}};

            var endTime = new Date(endDate.getFullYear(),endDate.getMonth(),endDate.getDate(), EhoursStart, EminutesStart, 0, 0);


            diff = Math.abs(endTime - startTime);

        } );
        </script>
@endsection
@section('extra-css')
<style>
		.container
			{
				min-width: 550px !important;
			}
		.zero-width {
			display:none;
			width: 0px;
			}
			
		table#example td {
			white-space: nowrap !important;
			overflow: hidden !important;
			text-overflow: ellipsis !important;
			-o-text-overflow: ellipsis !important;
			width: 10px;
			min-width:10px;
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
			min-width:50px !important;
			max-width:50px !important;
		}
		tfoot {
			display: table-header-group;
		}			

		.datepicker {
			padding:0px;
		}

/* CLASSES FOR USERS DATATABLE START */
		table#UsersExample td {
			white-space: nowrap !important;
			overflow: hidden !important;
			text-overflow: ellipsis !important;
			-o-text-overflow: ellipsis !important;
			width: 10px;
			min-width:10px;
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
			min-width:50px !important;
			max-width:50px !important;
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
				<h4 id="TeleTile">{{trans('conferences.conference')}}</h4>                       
         	</div>                   
            <div class="box" style="padding:30px 30px  20px 30px" >                
				<div class="row" style="margin:0;">
					@if ($errors->any())
						<ul class="alert alert-danger" style="margin: 0 15px 10px 15px">
						<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
							@foreach($errors->all() as $error)
								<li>{{ $error }}</li>
							@endforeach
						</ul>
					@endif
					{!! Form::model($conference, array('url' => 'conferences', 'method' => 'POST', 'class' => 'form-horizontal', 'id' => 'OrgForm', 'role' => 'form')) !!}
		      		@include('conferences._form', ['apella_id'=>$conference->apella_id,'start_date' => $conference->getDate($conference->start), 'start_time' => $conference->getTime($conference->start), 'end_date' => $conference->getDate($conference->end), 'end_time' => $conference->getTime($conference->end), 'max_duration' => null, 'max_users' => null, 'max_h323' => null, 'max_vidyo_room' => null, 'invisible' => Form::getValueAttribute('invisible'), 'submitBtn' => trans('conferences.saveConference'), 'copyBtn' => ''])
					{!! Form::hidden('copy_of', $conference->id) !!}
					{!! Form::close() !!}
				</div>                        
            </div>
			<!--/.box-->
			<!-- Form Details -END -->

		</div>
		<!--/.container-->
    </section>
@endsection
