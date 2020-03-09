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
	
	<link rel='stylesheet' type='text/css'href='/timepicki/css/timepicki.css'/>
    <script type='text/javascript'src='/timepicki/js/jquery.js'></script>
	<script type='text/javascript'src='/timepicki/js/timepicki.js'></script>
        
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



// ΣΥΝΑΡΤΗΣΕΙΣ ΣΧΕΤΙΚΕΣ ΜΕ ΤΗΝ ΑΠΟΣΤΟΛΗ EMAIL

	$('.summernote').summernote({
		lang: 'el-GR' 
	});
			
	$('#FieldMaintenanceStartTime').timepicki({
		show_meridian:false,
		min_hour_value:0,
		max_hour_value:23,
		step_size_minutes:15,
		overflow_minutes:true,
		increase_direction:'up',
		disable_keyboard_mobile: true,
		start_time: ["<?php echo $settings['default_start_hour']; ?>", "<?php echo $settings['default_start_min']; ?>"]
	}); 
	
	$('#FieldMaintenanceEndTime').timepicki({
		show_meridian:false,
		min_hour_value:0,
		max_hour_value:23,
		step_size_minutes:15,
		overflow_minutes:true,
		increase_direction:'up',
		disable_keyboard_mobile: true,
		start_time: ["<?php echo $settings['default_end_hour']; ?>", "<?php echo $settings['default_end_min']; ?>"]
	});

	$('.clockpicker').clockpicker();

	$('.datepicker').datepicker({
		format: "dd-mm-yyyy",
		todayBtn: "linked",
		language: "el",
		autoclose: true,
		todayHighlight: true
    });
		
	 $( "#InvEmail" ).click(function() {
		$("#InvEmailMessage").hide();						
  		$("#InvEmailModal").modal("show");
	});
	
	// $("#InvEmailSubmitBtn").click(function() {						
		// αποστολή email...και μετά από ελέγχους αποστολής το ανάλογο μύνημα
		 // $("#InvEmailMessage").text("H αποστολή του email πραγματοποιήθηκε με επιτυχία");
		 // $("#InvEmailMessage").removeClass( "alert-danger" ).addClass( "alert-info" );
			// $("#InvEmailMessage").show();
				 // window.setTimeout(function () {
				// $("#InvEmailModal").modal("hide");
			// }, 500);
	// });
	
	
		$("#NotifyParticipants").click(function() {
			$.get( "/settings/notifyParticipants")
				.done(function(data) {
					obj = JSON.parse(data);
					var oldvalue = obj.oldValue;
					if(obj.status == 'error'){
						alert( "" +obj.message );
						location.reload(true);
					} else if (obj.status == 'success'){
						alert( "" +obj.message );
					}
				})
				.fail( function(xhr, textStatus, errorThrown) {
					alert(xhr.responseText);
				});
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
                
                       
<!-- Tab line -START -->
            <div class="row">
                <div class="col-sm-12">            
                    <ul class="nav nav-tabs">
                          <li><a href="/conferences">{{trans('conferences.conferences')}}</a></li>
						  <li  class="active"><a href="#">{{trans('conferences.settings')}}</a></li>
                    </ul>
                </div>
            </div>   
<!-- Tab line -END -->

			<div class="small-gap"></div>
			@if (session('storesSuccessfully'))
				<div class="alert alert-success" style="margin: 0px 0px 10px 0px">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					{{ session('storesSuccessfully') }}
				</div>
			@endif
			
			@if ($errors->any())
				<ul class="alert alert-danger" style="margin: 0px 15px 10px 15px">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					@foreach($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			@endif
			
			{!! Form::open(array('url' => 'conferences/settings', 'method' => 'post', 'class' => 'form-horizontal', 'id' => 'SettingsForm', 'role' => 'form')) !!}
				<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
					<div class="panel panel-default noshadow">
                    <div class="panel-heading" role="tab" id="headingAdminEmail">
                      <h4 class="panel-title" >
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseAdminEmail" aria-expanded="true" aria-controls="collapseAdminEmail">
                           <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span> {{trans('conferences.participantsPerConference')}}
                        </a>
                      </h4>
                    </div>
                    <div id="collapseAdminEmail" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingAdminEmail">
                      <div class="panel-body" >
						  <div class="form-group">
							  {!! Form::label('FieldTotalResources', "Μέγιστος αριθμός", ['class' => 'control-label col-sm-4', 'style' => 'text-align:left']) !!}
							  <div class="col-sm-2">
								  {!! Form::text('conference_maxParticipants', $settings['conference_maxParticipants'], ['class' => 'form-control', 'id' => 'FieldTotalResources','disabled'=>true]) !!}
							  </div>
						  </div>
					  </div>
                    </div>
                  </div>
					<div class="panel panel-default noshadow">
						<div class="panel-heading" role="tab" id="headingUserActivation">
							<h4 class="panel-title">
								<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseActivation" aria-expanded="true" aria-controls="collapseActivation">
									<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span> {{trans('conferences.duration')}}
								</a>
							</h4>
						</div>
						<div id="collapseActivation" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingActivation">
							<div class="panel-body">
								<div class="form-group">
									{!! Form::label('FieldMaxDefaultDuration', trans('conferences.maxDurationMinutes'), ['class' => 'control-label col-sm-4', 'style' => 'text-align:left']) !!}
									<div class="col-sm-2">
										{!! Form::text('conference_maxDuration', $settings['conference_maxDuration'], ['class' => 'form-control', 'id' => 'FieldMaxDefaultDuration']) !!}
									</div>
								</div>
							</div>
						</div>
					</div>
                  <div class="panel panel-default noshadow">
                    <div class="panel-heading" role="tab" id="headingUserActivation">
                      <h4 class="panel-title">
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseActivation2" aria-expanded="true" aria-controls="collapseActivation2">
                           <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span> {{trans('conferences.availableResources')}}
                        </a>
                      </h4>
                    </div>
                    <div id="collapseActivation2" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingActivation">
                      <div class="panel-body">
						  <div class="form-group">
								{!! Form::label('FieldTotalResources', trans('conferences.totalResources'), ['class' => 'control-label col-sm-4', 'style' => 'text-align:left']) !!}
                                <div class="col-sm-2">
									{!! Form::text('conference_totalResources', $settings['conference_totalResources'], ['class' => 'form-control', 'id' => 'FieldTotalResources','disabled'=>true]) !!}
								</div>
                           </div>
						  <div class="form-group">
							  {!! Form::label('Field_max_h323_allowed', trans('conferences.max_h323_allowed'), ['class' => 'control-label col-sm-4', 'style' => 'text-align:left']) !!}
							  <div class="col-sm-2">
								  {!! Form::text('conference_H323Resources', $settings['conference_H323Resources'], ['class' => 'form-control', 'id' => 'Field_max_h323_allowed','disabled'=>true]) !!}
							  </div>
						  </div>
						  <div class="form-group">
							  {!! Form::label('Field_h323_allowed', trans('conferences.h323_connections_allowed'), ['class' => 'control-label col-sm-4', 'style' => 'text-align:left']) !!}
							  <div class="col-sm-2">
								  {!! Form::text('Field_h323_allowed', $settings['conference_H323Resources']-$settings['conference_EnabledH323IpDetection'], ['class' => 'form-control', 'id' => 'Field_h323_allowed','disabled'=>true]) !!}
							  </div>
						  </div>
						  <div class="form-group">
							  {!! Form::label('EnabledH323IpDetection', trans('conferences.h323_ip_detection'), ['class' => 'control-label col-sm-4', 'style' => 'text-align:left']) !!}
							  <div class="col-sm-2">
								  {!! Form::checkbox('conference_EnabledH323IpDetection', 1,$settings['conference_EnabledH323IpDetection'], ['id' => 'EnabledH323IpDetection']) !!}
							  </div>
						  </div>
					  </div>
                    </div>
                  </div>
                  <div class="panel panel-default noshadow">
                    <div class="panel-heading" role="tab" id="headingUserActivation">
                      <h4 class="panel-title">
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseActivation3" aria-expanded="true" aria-controls="collapseActivation3">
                           <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span> {{trans('conferences.systemMaintenance')}}
                        </a>
                      </h4>
                    </div>
                    <div id="collapseActivation3" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingActivation">
                      <div class="panel-body">
					  
							<div class="form-group" id="MaintenanceStartDiv">
								<label class="col-sm-2">{{trans('conferences.start')}}:</label>
                                <div style="float:left" >
									{!! Form::label('FieldMaintenanceStartDate', trans('conferences.date').':', ['class' => 'control-label col-sm-1']) !!}
                                </div>
                                <div style="float:left" >
                                	<div class="input-group date datepicker"  style="width:160px">
										{!! Form::text('maintenance_start_date', $settings['maintenance_start_date'], ['class' => 'form-control', 'id' => 'FieldMaintenanceStartDate']) !!}
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
   									</div>
								</div>
                                <div style="float:left" >
									{!! Form::label('FieldMaintenanceStartTime', trans('conferences.time').':', ['class' => 'control-label col-sm-1']) !!}
                                </div>
                                <div style="float:left" >                                
									{!! Form::text('maintenance_start_time', $settings['maintenance_start_time'], ['class' => 'form-control timepicker', 'id' => 'FieldMaintenanceStartTime', 'style' => 'width:50%;']) !!}
                                </div>
                            </div>   

							<div class="form-group" id="MaintenanceEndDiv">
								<label class="col-sm-2">{{trans('conferences.end')}}:</label>
                                <div style="float:left"  >
									{!! Form::label('FieldMaintenanceEndDate', trans('conferences.date').':', ['class' => 'control-label col-sm-1']) !!}
                                </div>
                                <div style="float:left" >
                                	<div class="input-group date datepicker"  style="width:160px">
										{!! Form::text('maintenance_end_date', $settings['maintenance_end_date'], ['class' => 'form-control', 'id' => 'FieldMaintenanceEndDate']) !!}
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
   									</div>
								</div>
                                <div style="float:left;">
									{!! Form::label('FieldMaintenanceEndTime', trans('conferences.time').':', ['class' => 'control-label col-sm-1']) !!}
                                </div>
                                <div style="float:left" >                                
									{!! Form::text('maintenance_end_time', $settings['maintenance_end_time'], ['class' => 'form-control timepicker', 'id' => 'FieldMaintenanceEndTime', 'style' => 'width:50%;']) !!}
                                </div> 
                            </div>
						   
						  @if($settings['send_email_btn'] == 'on')
							<div class="form-group">
								{!! Form::label('MaintenanceModerators', trans('conferences.maintenanceInformUsers').':', ['class' => 'control-label col-sm-2', 'style' => 'text-align:left']) !!}
                                <div class="col-sm-2">
									{!! Form::checkbox('emailToParticipants', intval($settings['maintenance_mode']), intval($settings['maintenance_mode']) == 1 ? true : false, ['id' => 'MaintenanceModerators', 'data-toggle' => 'checkbox-x', 'data-size' => 'lg', 'data-three-state' => 'false']) !!}
                                </div>
                            </div>
						  @endif
						  <hr>
                            <div class="form-group">
								{!! Form::label('FieldMaintenanceMode', trans('conferences.maintenanceActivation').':', ['class' => 'control-label col-sm-2', 'style' => 'text-align:left']) !!}
                                <div class="col-sm-2">
									{!! Form::checkbox('maintenance_mode', intval($settings['maintenance_mode']), intval($settings['maintenance_mode']) == 1 ? true : false, ['id' => 'FieldMaintenanceMode', 'data-toggle' => 'checkbox-x', 'data-size' => 'lg', 'data-three-state' => 'false']) !!}
                                </div>
                            </div>
							<div class="form-group" id="MaintenanceModeratorsDiv">
								{!! Form::label('FieldMaintenanceExcludeIPs', 'IPs to exclude:', ['class' => 'control-label col-sm-2', 'style' => 'text-align:left']) !!}
                                <div class="col-sm-6">
									{!! Form::textarea('maintenance_excludeIPs', $settings['maintenance_excludeIPs'], ['class' => 'form-control', 'size' => '200x3', 'id' => 'FieldMaintenanceExcludeIPs']) !!}
								</div>
                           </div>
                      </div>
                    </div>
                  </div>
<!-- Ενεργοποίηση Χρήστη End --> 
                           
                
                </div>
<!-- Collapse end---> 
    			<hr>
				{!! Form::submit(trans('conferences.saveAllSettings'), ['class' => 'btn btn-primary pull-right', 'name' => 'add_details', 'id' => 'InvEmailSubmitBtn']) !!}
				{!! Form::close() !!}
  

            </div><!--/.box-->
        </div><!--/.container-->        
    </section>
@endsection
