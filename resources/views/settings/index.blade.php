@extends('app')
@section('header-javascript')
	<link href="select2/select2.css" rel="stylesheet">
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
	
	<link rel='stylesheet' type='text/css' href='/timepicki/css/timepicki.css'/>
    <script type='text/javascript' src='/timepicki/js/jquery.js'></script>
	<script type='text/javascript' src='/timepicki/js/timepicki.js'></script>
        
    <link href="/css/main.css" rel="stylesheet">
	<link href="/css/eDatatables.css" rel="stylesheet">
	
	<script type="text/javascript" src="/datatables/jquery.dataTables.js"></script>
    <script type="text/javascript" src="/datatables/dataTables.bootstrap.js"></script>
	<link href="/datatables/dataTables.bootstrap.css" rel="stylesheet">

    
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->    
    


   	<script type="text/javascript">
		$(document).ready(function() {		
		
		
  		  		
// ΕΝΕΡΓΟΠΟΙΗΣΗ TOOLTIP

  		$('[data-toggle="tooltip"]').tooltip();

		$('.datepicker').datepicker({
		format: "dd-mm-yyyy",
		todayBtn: "linked",
		language: "el",
		autoclose: true,
		todayHighlight: true
		});
		
		$('.timepicker').timepicki({
		show_meridian:false,
		min_hour_value:0,
		max_hour_value:23,
		step_size_minutes:15,
		overflow_minutes:true,
		increase_direction:'up',
		disable_keyboard_mobile: true,
		reset: true
		});
		
		$('.summernote').summernote({
				lang: 'el-GR' 
			});
		
		$("#FieldMaintanceMode" ).on("change", function() {
				if ($("#FieldMaintanceMode").val()==0) {
					$("[id^=FieldMaintanceOn]").prop( "disabled", true );
				}else {	
					$("[id^=FieldMaintanceOn]").prop( "disabled", false );
				}
			});

// ΣΥΝΑΡΤΗΣΕΙΣ ΣΧΕΤΙΚΕΣ ΜΕ ΤΗΝ ΑΠΟΣΤΟΛΗ EMAIL
		
	  		$( "#InvEmail" ).click(function() {
				$("#InvEmailMessage").hide();						
  				$("#InvEmailModal").modal("show");
			});
	
			$("#InvEmailSubmitBtn").click(function() {						
				// αποστολή email...και μετά από ελέγχους αποστολής το ανάλογο μύνημα
				 $("#InvEmailMessage").text("{{trans('admin.emailSent')}}");
				 $("#InvEmailMessage").removeClass( "alert-danger" ).addClass( "alert-info" );
				 $("#InvEmailMessage").show();
				   window.setTimeout(function () {
					 $("#InvEmailModal").modal("hide");
					}, 500);
			});	
			
			
			
        } );
        </script>
@endsection
@section('extra-css')
<style>
		.container
			{
				min-width: 400px !important;
			}			
		.noshadow {
			-webkit-box-shadow: none;
			-moz-box-shadow: none;
			box-shadow: none;
			border:0px;
		}		

	
	</style>
@endsection

@section('conference-active')
	class = "active"
@endsection

@section('content')
	<section id="Users">
        <div class="container">
            <div class="box first" style="margin-top:100px">

			<div class="small-gap"></div>
			{!! Form::open(array('url' => 'settings', 'method' => 'post', 'class' => 'form-horizontal', 'id' => 'SettingsForm', 'role' => 'form')) !!}

<!-- Collapse start--->            
                <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                
                  <div class="panel panel-default noshadow">
                    <div class="panel-heading" role="tab" id="headingAdminEmail">
                      <h4 class="panel-title" >
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseAdminEmail" aria-expanded="true" aria-controls="collapseAdminEmail">
                           <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span> {{trans('admin.maintenanceMode')}}
                        </a>
                      </h4>
                    </div>
                    <div id="collapseAdminEmail" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingAdminEmail">
                      <div class="panel-body" >

                            <div class="form-group">
								{!! Form::label('FieldMaintanceMode', trans('admin.maintenanceMode').':', ['class' => 'control-label col-sm-3', 'style' => 'text-align:left']) !!}
                                <div class="col-sm-9">
									{!! Form::checkbox('maintenance_mode', intval(App\Settings::option("maintenance_mode")), true, ['id' => 'FieldMaintanceMode', 'data-toggle' => 'checkbox-x', 'data-size' => 'lg', 'data-three-state' => 'false']) !!}
                                </div>
                            </div>
							
							<div class="form-group">
								<label class="control-label col-sm-3" style="text-align:left">{{trans('admin.start')}}:</label>
                                <div style="float:left" >
									{!! Form::label('FieldMaintanceOnDateStart', trans('admin.date').':', ['class' => 'control-label col-sm-1']) !!}
                                </div>
                                <div style="float:left" >
                                	<div class="input-group date datepicker"  style="width:140px">
										{!! Form::text('maintenance_start_date', App\Settings::getDate(App\Settings::option("maintenance_start")), ['class' => 'form-control', 'id' => 'FieldMaintanceOnDateStart']) !!}
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
   									</div>
								</div>
                                <div style="float:left" >
									{!! Form::label('FieldMaintanceOnStartTime', trans('admin.time').':', ['class' => 'control-label col-sm-1']) !!}
                                </div>
                                <div style="float:left" >                                
									{!! Form::text('maintenance_start_time', App\Settings::getTime(App\Settings::option("maintenance_start")), ['class' => 'form-control timepicker', 'id' => 'FieldMaintanceOnStartTime', 'style' => 'width:50%;']) !!}
                                </div>
                            </div>   

							<div class="form-group">
								<label class="control-label col-sm-3" style="text-align:left">{{trans('admin.end')}}:</label>
                                <div style="float:left" >
									{!! Form::label('FieldMaintanceOnEndDate', rans('admin.date').':', ['class' => 'control-label col-sm-1']) !!}
                                </div>
                                <div style="float:left" >
                                	<div class="input-group date datepicker"  style="width:140px">
										{!! Form::text('maintenance_end_date', App\Settings::getDate(App\Settings::option("maintenance_end")), ['class' => 'form-control', 'id' => 'FieldMaintanceOnEndDate']) !!}
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
   									</div>
								</div>
                                <div style="float:left;">
									{!! Form::label('FieldMaintanceOnEndTime', trans('admin.time').':', ['class' => 'control-label col-sm-1']) !!}
                                </div>
                                <div style="float:left" >                                
									{!! Form::text('maintenance_end_time', App\Settings::getTime(App\Settings::option("maintenance_end")), ['class' => 'form-control timepicker', 'id' => 'FieldMaintanceOnEndTime', 'style' => 'width:50%;']) !!}
                                </div> 
                            </div>
							
							<div class="form-group" >
								<label for="FieldMaintanceMessage" class="ccontrol-label col-sm-3" style="text-align:left; margin-bottom:5px;">{{trans('admin.maintenanceMessage')}}:</label>
								<div class="col-sm-9">
									<!-- <div id="FieldInvMessage" class="summernote"></div> -->
									{!! Form::textarea('maintenance_message', App\Settings::option("maintenance_message"), ['class' => 'summernote', 'id' => 'FieldMaintanceMessage']) !!}
								</div>
							</div>
							
							<div class="form-group">
								{!! Form::label('FieldMaintanceModerators', trans('admin.informModerators').':', ['class' => 'control-label col-sm-3', 'style' => 'text-align:left']) !!}
                                <div class="col-sm-9">
									{!! Form::checkbox('maintenance_moderators', intval(App\Settings::option("maintenance_moderators")), true, ['id' => 'FieldMaintanceModerators', 'data-toggle' => 'checkbox-x', 'data-size' => 'lg', 'data-three-state' => 'false']) !!}
                                </div>
                           </div>
							
                      </div>
                    </div>
                  </div>    

<!-- Ενεργοποίηση Χρήστη Collapse --> 
                  <div class="panel panel-default noshadow">
                    <div class="panel-heading" role="tab" id="headingUserActivation">
                      <h4 class="panel-title">
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseActivation" aria-expanded="true" aria-controls="collapseActivation">
                           <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span> {{trans('admin.participantEmail')}}
                        </a>
                      </h4>
                    </div>
                    <div id="collapseActivation" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingActivation">
                      <div class="panel-body">
						
						<div class="form-group">
							{!! Form::label('FieldMaintanceMode2', trans('admin.maintenanceMode').':', ['class' => 'control-label col-sm-2 ']) !!}
							<div class="col-sm-10">
								{!! Form::checkbox('invisible', 0, true, ['id' => 'FieldMaintanceMode2', 'data-toggle' => 'checkbox-x', 'data-size' => 'lg', 'data-three-state' => 'false']) !!}
							</div>
						</div>
						
                      </div>
                    </div>
                  </div>
<!-- Ενεργοποίηση Χρήστη End --> 
                           
                
                </div>
<!-- Collapse end---> 
    			<hr>
				{!! Form::submit(('admin.saveAllSettings'), ['class' => 'btn btn-primary pull-right', 'name' => 'add_details', 'id' => 'InvEmailSubmitBtn']) !!}

				{!! Form::close() !!} 

            </div><!--/.box-->
        </div><!--/.container-->        
    </section>
@endsection
