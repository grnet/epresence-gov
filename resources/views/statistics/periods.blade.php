@extends('app')

@section('header-javascript')

	<!-- bootstrap date-picker    -->
	<script type="text/javascript" src="/bootstrap-datepicker-master/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="/bootstrap-datepicker-master/bootstrap-datepicker.el.js"></script>   
	<link href="/bootstrap-datepicker-master/datepicker3.css" rel="stylesheet">

	<!-- select-2    -->
    <link href="/select2/select2.css" rel="stylesheet">
    <script type="text/javascript" src="/select2/select2.js"></script>
	<script type="text/javascript" src="/select2/select2_locale_el.js"></script>

	<!-- charts    -->
	<script src="https://code.highcharts.com/highcharts.js"></script>
	<script src="https://code.highcharts.com/modules/data.js"></script>
	<script src="https://code.highcharts.com/modules/drilldown.js"></script>
	<!--
	<script src="/Highcharts-4.1.8/highcharts.js"></script>
    <script src="/Highcharts-4.1.8/modules/exporting.js"></script> 
    <script src="/Highcharts-4.1.8/modules/drilldown.js"></script> 
	-->
	
<script type="text/javascript">
$(document).ready(function() {		
	
	$("#FieldPeriod").select2({
		allowClear: false
	}); 
	
	
	

// DATE FIELD

	$('.datepicker').datepicker({
		format: "dd-mm-yyyy",
		todayBtn: "linked",
		language: "el",
		autoclose: true,
		todayHighlight: true
    });
			
// ΕΝΕΡΓΟΠΟΙΗΣΗ TOOLTIP

	$('[data-toggle="tooltip"]').tooltip();


	$('#Chart01').highcharts({
		@include('statistics._periodsBasicLineChart', ['data' => App\Statistics::participant_per_conference($dates, $group)])
	});
	
	$('#Chart02').highcharts({
		@include('statistics._periodsDualAxesChart', ['data' => App\Statistics::duration_per_conference($dates, $group)])
	});
	
	$('#Chart03').highcharts({
		@include('statistics._periodsBasicBarChart', ['data' => App\Statistics::conferences_duration($dates, $group)])
	});
	
	$('#Chart04').highcharts({
		@include('statistics._periodsBasicBarChart', ['data' => App\Statistics::conference_participants($dates, $group)])
	});
	
	$('#Chart05').highcharts({
		@include('statistics._periodsColumnWithDrilldownChart', ['data' => App\Statistics::conferences_per_institution($statistics), 'graph_title' => trans('statistics.noConfsPerInst')])
	});
	
	$('#Chart06').highcharts({
		@include('statistics._periodsColumnWithDrilldownChart', ['data' => App\Statistics::conference_duration_per_institution($statistics), 'graph_title' => trans('statistics.confDurationPerInst')])
	});
	
});
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
		
		.chartbox {
			border:1px solid #F7F7F7; 
			padding:10px;
		}
		
		.datepicker {
			padding:0px;
		}
		.formDiv {
			float:left; 
			margin-left:10px;
		}
	
	</style>

@endsection

@section('statistics-active')
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
						<li><a href="/statistics">{!!trans('statistics.realTimeUsage')!!}</a></li>
                        <li class="active"><a href="/statistics/periods">{!!trans('statistics.pastPeriodUsage')!!}</a></li>
						<li><a href="/statistics/personalized">{!!trans('statistics.personalized')!!}</a></li>
						@if(Auth::user()->hasRole('SuperAdmin'))
							<li><a href="/statistics/report">{!!trans('statistics.usageReports')!!}</a></li>
							<li><a href="/statistics/demo-room">Demo Room</a></li>
							<li><a href="/statistics/utilization">Utilization</a></li>
						@endif
                    </ul>
                </div>
            </div>   
<!-- Tab line -END -->

			<div class="small-gap"></div>
			@if ($errors->any())
			<ul class="alert alert-danger" style="margin: 0px 15px 10px 15px">
				<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				@foreach($errors->all() as $error)
				<li>{{ $error }}</li>
					@endforeach
				</ul>
			@endif
            <div  style="background-color:#F7F7F7; padding:10px;">
				{!! Form::open(array('url' => '/statistics/periods', 'method' => 'get', 'class' => 'form-horizontal', 'id' => 'OrgForm', 'role' => 'form')) !!}

                	<div class="row">
            			<div class="col-sm-12">
                        	<div style="float:left">
								<label class="control-label">{!!trans('statistics.definePeriod')!!}:</label>
                            </div>
                            <div class="visible-sm visible-xs" style="clear:both"></div>
                    		<div class="formDiv">{!! Form::label('FieldStartDate', trans('statistics.from').':', ['class' => 'control-label']) !!}</div>
							
                            <div class="formDiv">
                                <div class="input-group date datepicker"  style="width:140px">
									{!! Form::text('start', $start_from, ['class' => 'form-control', 'id' => 'FieldStartDate']) !!}
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                                </div>
                            </div>
                            <div class="visible-xs" style=" clear:both"></div>             
                        	<div class="formDiv">{!! Form::label('FieldEnd', trans('statistics.upto').':', ['class' => 'control-label']) !!}</div>
                        	<div class="formDiv">
                            	<div class="input-group date datepicker" style="width:140px">
									{!! Form::text('end', $end_from, ['class' => 'form-control', 'id' => 'FieldEnd']) !!}
                                	<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                            	</div>
                        	</div>
                            <div class="visible-xs" style=" clear:both"></div>
                            <div class="formDiv" >{!! Form::label('FieldPeriod', trans('statistics.intervalBy').':', ['class' => 'control-label']) !!}</div>
                            <div class="formDiv" >
							{!! Form::select('select_period', ['day' => trans('statistics.byDay'), 'month' => trans('statistics.byMonth'), 'year' => trans('statistics.byYear')], $selected_period, ['id' => 'FieldPeriod', 'style' => 'width:100px'])!!}
                            </div>
                            <div class="visible-sm visible-xs" style=" clear:both"></div>             
                        	<div class="formDiv">
								{!! Form::submit(trans('statistics.refreshGraphs'), ['class' => 'btn btn-info', 'id' => 'TeleSave', 'aria-hidden' => 'true']) !!}
							</div>       
						</div>
					</div>
				{!! Form::close() !!}
			</div>  

			<div class="small-gap"></div>
            <div class="row">
            	<div class="col-md-6">
                	<div id="Chart01" class="chartbox"></div>
                </div>
                <div class="col-md-6">
					<div id="Chart02" class="chartbox"></div>
                </div>
            </div>
			
			<div class="small-gap"></div>
            <div class="row">
            	<div class="col-md-6">
                	<div id="Chart03" class="chartbox"></div>
                </div>
                <div class="col-md-6">
					<div id="Chart04" class="chartbox"></div>
                </div>
            </div>
            
            <div class="small-gap"></div>
            <div class="row">
            	<div class="col-md-12">
                	<div id="Chart05" class="chartbox"></div>
                </div>
            </div>
			
			<div class="small-gap"></div>
            <div class="row">
            	<div class="col-md-12">
                	<div id="Chart06" class="chartbox"></div>
                </div>
            </div>
			
            
            </div><!--/.box-->
        </div><!--/.container-->        
    </section>
@endsection
