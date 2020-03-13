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
	<!--[if lt IE 9]>
	<script src="js/html5shiv.js"></script>
	<script src="js/respond.min.js"></script>
	<![endif]-->

	<script type="text/javascript">
		$(document).ready(function() {

// ΕΝΕΡΓΟΠΟΙΗΣΗ TOOLTIP

			$('[data-toggle="tooltip"]').tooltip();

// ΛΕΠΤΟΜΕΡΕΙΕΣ ΤΗΛΕΔΙΑΣΚΕΨΗΣ

			$("#TeleDetailsAlert").hide();
			$("#TeleSaveDetails").hide();
			$("#ParticipatsTitle").show();
			$("#ParticipatsBody").show();
			$("#ParticipatsBody").show();
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


			$("#GotoTop").click(function() {
				var gotopage=$("#ParticipatsTitle").offset().top;
				$("html, body").animate({ scrollTop: gotopage});
			});

			$('#FieldStartDate').on('change', function(){
				var hdate=$('#FieldStartDate').val();
				if($('#FieldEndDate').val()==""){
					$('#FieldEndDate').val(hdate);
				}
			});

			$('#FieldInvMessage').summernote({
				lang: 'el-GR'
			});

			$( "#InvEmail" ).click(function() {
				$("#InvEmailMessage").hide();
				$("#InvEmailModal").modal("show");
			});

			$("#InvEmailSubmitBtn").click(function() {
				// αποστολή email...και μετά από ελέγχους αποστολής το ανάλογο μύνημα
				$("#InvEmailMessage").text("{{trans('conferences.emailSent')}}");
				$("#InvEmailMessage").removeClass( "alert-danger" ).addClass( "alert-info" );
				$("#InvEmailMessage").show();
				window.setTimeout(function () {
					$("#InvEmailModal").modal("hide");
				}, 500);
			});

		// ΣΥΝΑΡΤΗΣΕΙΣ ΣΧΕΤΙΚΕΣ ΜΕ participantsTable

			//  Ορισμός Πίνακα DataTable - {"bVisible": false} για να κρύψουμε πχ rec-no
			$("[id^=openParticipantDetails]").on("click", function() {
				var user = $(this).attr('id').split('-').pop(-1);
				if($("#participantDetails-"+user).hasClass("out")) {
					$("#participantDetails-"+user).addClass("in");
					$("#participantDetails-"+user).removeClass("out");
				} else if($("#participantDetails-"+user).hasClass("in")) {
					$("#participantDetails-"+user).addClass("out");
					$("#participantDetails-"+user).removeClass("in");
				}else{
					$("#participantDetails-"+user).addClass("in");
				}
			});

			// Sort table
			
			// Get url parameters
			$.urlParam = function(name){
				var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
				return results[1] || 0;
			}
			
			// Default class
			var sortings = ["lastname", "email", "state"];
			var url = window.location.href;
			$.each(sortings, function( index, value ){
				if(url.search("sort_"+value) > 0 && value != "lastname"){
					$("#sort_"+value).removeClass("sorting");
					$("#sort_"+value).addClass("sorting"+ $.urlParam("sort_"+value));
					$("#sort_lastname").removeClass("sortingasc");
					$("#sort_lastname").addClass("sorting");
				}else if(url.search("sort_"+value) > 0 && value == "lastname"){
					$("#sort_"+value).removeClass("sortingdesc");
					$("#sort_"+value).addClass("sorting"+ $.urlParam("sort_"+value));
				}
			});
			
			
			$("[id^=sort]").on("click", function() {
				var sortings = ["lastname", "email", "state"];
				var col = $(this).attr('id').split('_').pop(-1);
				var url = window.location.href;
				var url_pathname = window.location.pathname;
				// alert(url.search("sort_" + col));
				var current_param = window.location.search.substring(1);
				if(current_param != null){
					current_param = current_param + "&";
				}
				
				if(url.search("sort_" + col) < 0){
					$.each( sortings, function( index, value ){
						current_param = current_param.replace("&sort_"+value+"=asc", "");
						current_param = current_param.replace("&sort_"+value+"=desc", "");
					});
					var params = [{name: "sort_"+col, value: "asc"}];
					window.location.assign(url_pathname + "?" + current_param + $.param(params));
				}
				
				else if(url.search("sort_" + col) > 0){
					var variable = $.urlParam("sort_" + col);
					var new_var = "desc";
					if(variable === "desc"){
						var new_var = "asc";
					}
					$.each( sortings, function( index, value ){
						current_param = current_param.replace("&sort_"+value+"=asc", "");
						current_param = current_param.replace("&sort_"+value+"=desc", "");
					});
					current_param = current_param.replace("&sort_"+col+"="+variable, "");
					var params = [{name: "sort_"+col, value: new_var}];
					window.location.assign(url_pathname + "?" + current_param + $.param(params));
				} 
			});


// ΣΥΝΑΡΤΗΣΕΙΣ ΣΧΕΤΙΚΕΣ EXPORT

			function exportTableToCSV($table, filename) {

				var $rows = $table.find('tr:has(td,th)'),

				// Temporary delimiter characters unlikely to be typed by keyboard
				// This is to avoid accidentally splitting the actual contents
						tmpColDelim = String.fromCharCode(11), // vertical tab character
						tmpRowDelim = String.fromCharCode(0), // null character

				// actual delimiter characters for CSV format
						colDelim = '","',
						rowDelim = '"\r\n"',

				// Grab text from table into CSV formatted string
						csv = '"' + $rows.map(function (i, row) {
									var $row = $(row),
											$cols = $row.find('td,th');

									return $cols.map(function (j, col) {
										var $col = $(col),
												text = $col.text();

										return text.replace(/"/g, '""'); // escape double quotes

									}).get().join(tmpColDelim);

								}).get().join(tmpRowDelim)
										.split(tmpRowDelim).join(rowDelim)
										.split(tmpColDelim).join(colDelim) + '"',

				// Data URI
						csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);

				$(this)
						.attr({
							'download': filename,
							'href': csvData,
							'target': '_blank'
						});
			}

			// This must be a hyperlink
			$("#export").on('click', function (event) {
				// CSV
				var $twra=new Date($.now());
				$twra = $twra.toDateString();
				var filename = '{{ $conference->title }}'+$twra+'.csv';
				filename = filename.replace(" ", "_");
				exportTableToCSV.apply(this, [$('#example'), filename]);

				// IF CSV, don't do event.preventDefault() or return false
				// We actually need this to be a typical hyperlink
			});
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

		table#participantsTable th {
			font-size:12px;
			white-space: nowrap !important;
			overflow: hidden !important;
			text-overflow: ellipsis !important;
			-o-text-overflow: ellipsis !important;
		}
			
		table#participantsTable td {
			white-space: nowrap;
			overflow: hidden !important;
			text-overflow: ellipsis !important;
			-o-text-overflow: ellipsis !important;
		}
		
		table#participantsTable th {
			font-size:12px;
			white-space: nowrap !important;
			overflow: hidden !important;
			text-overflow: ellipsis !important;
			-o-text-overflow: ellipsis !important;
		}
			
		table#participantsTable td {
			white-space: nowrap;
			overflow: hidden !important;
			text-overflow: ellipsis !important;
			-o-text-overflow: ellipsis !important;
		}
		
		table#participantsTable thead .sorting:before {
			font-family: 'Glyphicons Halflings';
			content: "\e150";
			padding: 0px 2px;
			font-size: 0.8em;
			color: #52b6ec;
		}
		
		table#participantsTable thead .sortingasc:before {
			font-family: 'Glyphicons Halflings';
			content: "\e155";
			padding: 0px 2px;
			font-size: 0.8em;
			color: #52b6ec;
		}
		
		table#participantsTable thead .sortingdesc:before {
			font-family: 'Glyphicons Halflings';
			content: "\e156";
			padding: 0px 2px;
			font-size: 0.8em;
			color: #52b6ec;
		}
		
		table#participantsTable thead .sorting, table thead .sortingasc, table thead .sortingdesc{
			cursor:pointer;
		}

		#participantsTable{
			table-layout: fixed;
		}

		@media (max-width: 767px) {
			#participantsTable {
				table-layout: auto;
			}
		}

		.participant_details{
			cursor:pointer;
		}
		.hiddenRow {
			padding: 0 !important;
		}

		.cellDetails {
			width: 5% !important;
		}
		.cellPName {
			width: 25% !important;
			word-wrap: break-word;
			white-space: normal!important;
		}
		.cellPRole {
			width: 12.5% !important;
		}
		.cellPEmail {
			width: 17.5% !important;
		}
		.cellPState {
			width: 7.5% !important;
		}
		.cellPDevice {
			width: 12.5% !important;
		}
		.cellPStatus {
			width: 12.5% !important;
		}
		.cellPAddress {
			width: 12.5% !important;
			word-wrap: break-word;
			white-space: normal!important;
		}
		.cellPDuration {
			width: 10.5% !important;
		}
		tfoot {
			display: table-header-group;
		}
		.datepicker {
			padding:0;
		}
		.spanConnectionIntervals {
			white-space: pre-line;
		}

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
				<div class="row" style="margin:0px;">
					<div class="row">
						<div class="control-label col-sm-2 col-xs-12">
							<strong>{{trans('conferences.conferenceID')}}:</strong>
						</div>
						<div class="col-sm-4">
							{{ $conference->id }}
						</div>
					</div>
					<div class="small-gap"></div>
					<div class="row">
						<div class="control-label col-sm-2 col-xs-12">
							<strong>{{trans('conferences.participants')}}:</strong>
						</div>
						<div class="col-sm-3 col-xs-12 .col-xs-offset-6">
							Desktop-Mobile: {{ $conference->participantsPerDevice('Desktop-Mobile') }}
						</div>
					</div>
					<div class="small-gap"></div>

					<div class="row">
						<div class="control-label col-sm-2 col-xs-12">
							<strong>{{trans('conferences.description')}}:</strong>
						</div>
						<div class="col-sm-4">
							{{ $conference->title }}
						</div>
					</div>

					<div class="small-gap"></div>

					<div class="row">
						<div class="control-label col-sm-2 col-xs-12">
							<strong>{{trans('conferences.moderator')}}:</strong>
						</div>
						<div class="col-sm-6">
							<p>
								{{ $conference->user->lastname }} {{  $conference->user->firstname }}
								@if($conference->user->hasRole('SuperAdmin'))
									<i><small> — {{trans('conferences.admin')}}</small></i>
								@elseif($conference->user->hasRole('InstitutionAdministrator') || $conference->user->hasRole('DepartmentAdministrator'))
									<i><small> — {{$conference->user->institutions->first()->title }} 
									@if($conference->user->hasRole('DepartmentAdministrator'))
										&nbsp;({{ $conference->user->departments->first()->title }})
									@endif
									</small></i>
								@endif
							</p>
						</div>
					</div>

					<div class="small-gap"></div>

					<div class="row">
						<div class="control-label col-sm-2 col-xs-12">
							<strong>{{trans('conferences.start')}}:</strong>
						</div>
						<div class="col-sm-1">
							{{trans('conferences.date')}}:
						</div>
						<div class="col-sm-2">
							{{ $conference->getDate($conference->start) }} <i class="glyphicon glyphicon-calendar"></i>
						</div>
						<div class="col-sm-1">
							{{trans('conferences.time')}}:
						</div>
						<div class="col-sm-2">
							{{ $conference->getTime($conference->start) }} <span class="glyphicon glyphicon-time"></span>
						</div>
					</div>

					<div class="small-gap"></div>

					<div class="row">
						<div class="control-label col-sm-2 col-xs-12">
							<strong>{{trans('conferences.end')}}:</strong>
						</div>
						<div class="col-sm-1">
							{{trans('conferences.date')}}:
						</div>
						<div class="col-sm-2">
							{{ $conference->getDate($conference->end) }} <i class="glyphicon glyphicon-calendar"></i>
						</div>
						<div class="col-sm-1">
							{{trans('conferences.time')}}:
						</div>
						<div class="col-sm-2">
							{{ $conference->getTime($conference->end) }} <span class="glyphicon glyphicon-time"></span>
						</div>
					</div>
					@if($conference->forced_end)
						<div class="small-gap"></div>
						<div class="row">
							<div class="control-label col-sm-2 col-xs-12">
								<strong>{!!trans('conferences.terminatedBeforeEnd')!!}</strong>
							</div>
							<div class="col-sm-1">
								{{trans('conferences.date')}}:
							</div>
							<div class="col-sm-2">
								{{ $conference->getDate($conference->forced_end) }} <i class="glyphicon glyphicon-calendar"></i>
							</div>
							<div class="col-sm-1">
								{{trans('conferences.time')}}:
							</div>
							<div class="col-sm-2">
								{{ $conference->getTime($conference->forced_end) }} <span class="glyphicon glyphicon-time"></span>
							</div>
						</div>
					@endif
					<div class="small-gap"></div>
					<div class="row">
						<div class="col-sm-12">
							<span class="pull-right">
								<a href="/conferences/{{ $conference->id }}/copy"><button type="button" class="btn btn-warning" id="TeleCopy" >{{trans('conferences.conferenceCopy')}}</button></a>
								<a href="{{ Session::get('previous_url') }}"><button type="button" class="btn btn-default" id="TeleReturn" >{{trans('conferences.return')}}</button></a>
							</span>
						</div>
					</div>
				</div>
			</div><!--/.box-->
			<!-- Form Details -END -->
			<!-- SYMMETEXONTES START -->
			@if($conference->room_enabled == 0 && ($conference->end->lt(Carbon\Carbon::now()) || (!empty($conference->forced_end) && $conference->forced_end->lt(Carbon\Carbon::now()) )))
				<div class="small-gap"></div>
				<div id="ParticipatsTitle" class="box" style="padding:0; background-color:transparent;">
					<h4>
						{{trans('conferences.participantList')}}
					</h4>
				</div>
				
				<div class="box" id="ParticipatsBody">
					@include('conferences._participantsTable', ['sort' => Input::get()])
					<!--	<a href="#" id="export">Εξαγωγή σε csv</a> -->
				</div><!--/.box-->
			@else
				<h4>{{trans('conferences.confNotFinished')}}</h4>
			@endif
		</div>
		<!-- SYMMETEXONTES END -->
		<div class="small-gap"></div>
		<!--/.container-->

	</section>
@endsection
