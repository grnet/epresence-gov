@extends('app')

@section('header-javascript')
    <link rel="shortcut icon" href="/images/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/images/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/images/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/images/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="/images/ico/apple-touch-icon-57-precomposed.png">
    

	<script src="/js/jquery-2.1.4.js"></script>    
      
    <link href="/bootstrap-3.1.1-dist/css/bootstrap.css" rel="stylesheet">
	<script src="/bootstrap-3.1.1-dist/js/bootstrap.min.js"></script>
    

	<link rel="stylesheet" href="/bootstrap-calendar/css/calendar.css">
	<script type="text/javascript" src="/bootstrap-calendar/components/underscore/underscore-min.js"></script>
	<script type="text/javascript" src="/bootstrap-calendar/components/jstimezonedetect/jstz.min.js"></script>

	<script type="text/javascript" src="/bootstrap-calendar/js/language/el-GR.js"></script>

    <link href="/css/main.css" rel="stylesheet">

	
	<script type="text/javascript">
		$(document).ready(function() {
		
			$('[data-toggle="tooltip"]').tooltip();
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
	</style>
@endsection
@section('calendar-active')
class="active"
@endsection

@section('content')
    <section id="Users">
        <div class="container">
            <div class="box first" style="margin-top:100px; min-height:200px">

			<div class="small-gap"></div>

			<div class="page-header" >
                <div class="pull-right form-inline">
                      <div class="btn-group">
						<button class="btn btn-default" id="cal-prev"><span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span></button>
                        <button class="btn btn-info active" id="cal-month">{{trans('site.month')}}</button>
						<button class="btn btn-default" id="cal-next"><span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span></button>
					</div>
                </div>

				<h3></h3>
			</div>

            <div class="row">
                <div class="col-md-12">
                    <div id="calendardiv"></div>
                </div>
            </div>		  

            </div><!--/.box-->
        </div><!--/.container-->        
    </section>
	

	<script type="text/javascript" src="/bootstrap-calendar/js/calendar.js"></script>
    <script type="text/javascript" src="/bootstrap-calendar/js/app.js"></script>
	<script type="text/javascript">


		var lng_code = '';
		var lng = '{!! Session::get('locale') !!}';
		if(lng === 'en'){
			lng_code = 'en-EN';
		}
		else if (lng === 'el'){
			lng_code = 'el-GR';
		}


			var now = new Date();
			var strDate = now.getFullYear() + '-' + (("0" + (now.getMonth() + 1)).slice(-2)) + '-' + (("0" + (now.getDate() + 1)).slice(-2));
			var calendar = $('#calendardiv').calendar({
				events_source: '/calendar/json',
				language: lng_code,
				first_day: '1',
				view: 'month',
				tmpl_path: 'bootstrap-calendar/tmpls/',
				tmpl_cache: false,
				day: strDate,
				onAfterEventsLoad: function (events) {
					if (!events) {
						return;
					}
					var list = $('#eventlist');
					list.html('');

					$.each(events, function (key, val) {
						$(document.createElement('li'))
								.html('<a href="' + val.url + '" >' + val.title + ' </a>')
								.appendTo(list);

					});

				},
				onAfterViewLoad: function (view) {
					$('.page-header h3').text(this.getTitle());
					$('.btn-group button').removeClass('active');
					$('button[data-calendar-view="' + view + '"]').addClass('active');

				},
				classes: {
					months: {
						general: 'label'
					}
				}
			});

			$( "#cal-prev" ).click(function() {
				calendar.navigate('prev');
			});
			$( "#cal-next" ).click(function() {
				calendar.navigate('next');
			});
			$( "#cal-today" ).click(function() {
				calendar.navigate('today');
			});
			$( "#cal-month" ).click(function() {
				calendar.view('month');
			});




	</script>

@endsection
