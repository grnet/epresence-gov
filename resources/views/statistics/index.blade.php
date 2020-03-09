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
	</style>
	<!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
@include('statistics.index_chart_scripts')
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
						<li class="active"><a href="/statistics">{!!trans('statistics.realTimeUsage')!!}</a></li>
                        <li><a href="/statistics/periods">{!!trans('statistics.pastPeriodUsage')!!}</a></li>
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
			<div style="text-align:right">
				<button type="button" class="btn btn-primary" style="padding-right:6px; padding-left:6px" id="RefreshPage"><span class="glyphicon glyphicon-refresh"></span> {!!trans('statistics.refreshPage')!!}</button>
			</div>
			<div class="small-gap"></div>
            <div  class="alert alert-info">
				{!!trans('statistics.realTimeStats')!!}
			</div>
			
			<div class="small-gap"></div>
            <div class="row">
            	<div class="col-md-12">
                	<div id="Chart01" class="chartbox"></div>
                </div>
            </div>

			<div class="small-gap"></div>
			<div class="row gause">
            	<div class="col-md-4">
                	<div id="Chart02" class="chartbox"></div>
                </div>
                <div class="col-md-4">
					<div id="Chart03" class="chartbox"></div>
                </div>
				<div class="col-md-4">
                	<div id="Chart04" class="chartbox"></div>
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
            </div><!--/.box-->
        </div><!--/.container-->        
    </section>
@endsection
