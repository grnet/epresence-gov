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
	
	<script src="https://code.highcharts.com/highcharts.js"></script>
	<script src="https://code.highcharts.com/highcharts-more.js"></script>
	<script src="https://code.highcharts.com/modules/data.js"></script>
	<script src="https://code.highcharts.com/modules/drilldown.js"></script>
	<script src="https://code.highcharts.com/modules/solid-gauge.js"></script>

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
		span.period_reports{
			font-weight: 900 !important;
		}

	</style>

    

    
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->    
    


<script type="text/javascript">
$(document).ready(function() {		
	
	$("#FieldPeriod").select2({
		allowClear: false
	}); 
	
	
	$("#RefreshPage").click(function() {						
				location.reload(true);
			});	

// DATE FIELD

	$('.datepicker').datepicker({
		format: "dd-mm-yyyy",
		todayBtn: "linked",
		language: "el",
		autoclose: true,
		todayHighlight: true
    });



	$("#updateRef").submit(function(event) {
		event.preventDefault();
		var to = $("#FieldStartDate").val();
		var period = $("#FieldPeriod").val();
        $("#loading_icon").show();
		$.get( "/statistics/report_select_period", { to: to, period: period})
			.done(function( data ) {
				$("#referenceText").html(data);
                $("#loading_icon").hide();
			}).fail( function(xhr, textStatus, errorThrown) {
						alert(xhr.responseText);
					});	
		});
			
// ΕΝΕΡΓΟΠΟΙΗΣΗ TOOLTIP

	$('[data-toggle="tooltip"]').tooltip();

	
// CHART: Αριθμός συμμετεχόντων σε κάθε δωμάτιο

   $('#Chart01').highcharts({
		@include('statistics._BasicLineChart', ['data' => $data['chart_1'],'periods'=>$data['periods']])
	});

	$('#Chart02').highcharts({
		@include('statistics._BasicColumnChart', ['data' => $data['chart_2'],'periods'=>$data['periods']])
	});

	$('#Chart03').highcharts({
		@include('statistics._DualAxesChart', ['data' => $data['chart_3'],'periods'=>$data['periods'], 'graph_title' => trans('statistics.instWithOneConf'), 'column_title' => trans('statistics.instIncr').' %', 'column_valueSuffix' => ' %', 'spline_title' => trans('statistics.institutionsCap'), 'spline_valueSuffix' => ' '.trans('statistics.institutions'), 'color' => '90ed7d'])
	});

	$('#Chart04').highcharts({
		@include('statistics._DualAxesChart', ['data' => $data['chart_4'],'periods'=>$data['periods'], 'graph_title' => trans('statistics.deptWithOneConf'), 'column_title' => trans('statistics.deptIncr').' %', 'column_valueSuffix' => ' %', 'spline_title' => trans('statistics.departmentsCap'), 'spline_valueSuffix' => ' '.trans('statistics.departments'), 'color' => '90ed7d'])
	});

	$('#Chart05').highcharts({
		@include('statistics._DualAxesChart', ['data' => $data['chart_5'],'periods'=>$data['periods'], 'graph_title' => trans('statistics.uniqueDesktopUsers'), 'column_title' => trans('statistics.desktopIncr').' %', 'column_valueSuffix' => ' %', 'spline_title' => trans('statistics.usersCap'), 'spline_valueSuffix' => ' '.trans('statistics.users'), 'color' => '7cb5ec'])
	});

	$('#Chart06').highcharts({
		@include('statistics._DualAxesChart', ['data' => $data['chart_6'],'periods'=>$data['periods'], 'graph_title' => trans('statistics.uniqueH323Users'), 'column_title' => trans('statistics.h323Incr').' %', 'column_valueSuffix' => ' %', 'spline_title' => trans('statistics.usersCap'), 'spline_valueSuffix' => ' '.trans('statistics.users'), 'color' => 'f7a35c'])
	});

	$('#Chart07').highcharts({
		@include('statistics._DualAxesChart', ['data' => $data['chart_7'],'periods'=>$data['periods'], 'graph_title' => trans('statistics.maxConcurrentDesktop'), 'column_title' => trans('statistics.maxDeskIncr').' %', 'column_valueSuffix' => ' %', 'spline_title' => trans('statistics.usersCap'), 'spline_valueSuffix' => ' '.trans('statistics.users'), 'color' => '7cb5ec'])
	});

	$('#Chart08').highcharts({
		@include('statistics._DualAxesChart', ['data' => $data['chart_8'],'periods'=>$data['periods'], 'graph_title' => trans('statistics.maxConcurrentH323'), 'column_title' => trans('statistics.maxH323Incr').' %', 'column_valueSuffix' => ' %', 'spline_title' => trans('statistics.usersCap'), 'spline_valueSuffix' => ' '.trans('statistics.users'), 'color' => 'f7a35c'])
	});

});
</script>
@endsection
@section('extra-css')

@endsection

@section('statistics-active')
class="active"
@endsection

@section('content')

<section id="Users">
        <div class="container">
            <div class="box first" style="margin-top:100px">
                       
<!-- Tab line -START -->
            <div class="row">
                <div class="col-sm-12">            
                    <ul class="nav nav-tabs">
						<li><a href="/statistics">{{trans('statistics.realTimeUsage')}}</a></li>
                        <li><a href="/statistics/periods">{{trans('statistics.pastPeriodUsage')}}</a></li>
						<li><a href="/statistics/personalized">{!!trans('statistics.personalized')!!}</a></li>
						@if(Auth::user()->hasRole('SuperAdmin'))
							<li class="active"><a href="/statistics/report">{!!trans('statistics.usageReports')!!}</a></li>
							<li><a href="/statistics/demo-room">Demo Room</a></li>
							<li><a href="/statistics/utilization">Utilization</a></li>
						@endif
                    </ul>
                </div>
            </div>   
<!-- Tab line -END -->
			
			<div class="small-gap"></div>
            <div class="row">
            	<div class="col-md-12">
                	<div id="Chart01" class="chartbox"></div>
                </div>
            </div>
			
			<div class="small-gap"></div>
            <div class="row">
            	<div class="col-md-12">
                	<div id="Chart02" class="chartbox"></div>
                </div>
            </div>
			
			<div class="small-gap"></div>
            <div class="row">
            	<div class="col-md-12">
                	<div id="Chart03" class="chartbox"></div>
                </div>
            </div>
			
			<div class="small-gap"></div>
            <div class="row">
            	<div class="col-md-12">
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
			
			<div class="small-gap"></div>
            <div class="row">
            	<div class="col-md-12">
                	<div id="Chart07" class="chartbox"></div>
                </div>
            </div>
			
			<div class="small-gap"></div>
            <div class="row">
            	<div class="col-md-12">
                	<div id="Chart08" class="chartbox"></div>
                </div>
            </div>
			<hr/>
			<div class="small-gap"></div>
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="row">
						<div class="col-md-4" style="padding:7px;">
							{{trans('statistics.quarterReport')}}
						</div>
						<div class="col-md-8">
							{!! Form::open(array('method' => 'get' ,'class' => 'form-horizontal', 'id' => 'updateRef', 'role' => 'form')) !!}
								<div class="row">
									<div class="col-sm-12">
										<div class="visible-sm visible-xs" style="clear:both"></div>
										<div class="formDiv">{!! Form::label('FieldStartDate', trans('statistics.upto').':', ['class' => 'control-label']) !!}</div>

										<div class="formDiv">
											<div class="input-group date datepicker"  style="width:140px">
												{!! Form::text('to', Carbon\Carbon::today()->format('d-m-Y'), ['class' => 'form-control', 'id' => 'FieldStartDate']) !!}
												<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
											</div>
										</div>
										<div class="visible-xs" style=" clear:both"></div>
										<div class="formDiv">{!! Form::label('FieldPeriod', trans('statistics.selectPeriod').':', ['class' => 'control-label']) !!}</div>
										<div class="formDiv">
										{!! Form::select('period', [3 => trans('statistics.quarter'), 6 => trans('statistics.halfYear')], 3, ['id' => 'FieldPeriod', 'style' => 'width:100px'])!!}
										</div>
										<div class="visible-sm visible-xs" style=" clear:both"></div>
										<div class="formDiv">
											{{--{!! Form::submit(trans('statistics.refreshReport'), ['class' => 'btn btn-info', 'aria-hidden' => 'true']) !!}--}}
											<button class="btn btn-info" aria-hidden="true" type="submit">{!! trans('statistics.refreshReport') !!} </button>
											<img src="/images/loader_gif.gif" style="display:none; width:30px;" id="loading_icon">
										</div>
									</div>
								</div>
							{!! Form::close() !!}
						</div>
					</div>
				</div>
				<div class="panel-body" id="referenceText">
					@include('statistics.text_reports_template')
				</div>
			</div>

            </div><!--/.box-->
        </div><!--/.container-->        
    </section>
@endsection
