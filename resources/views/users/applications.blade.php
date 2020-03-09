@extends('app')

@section('header-javascript')

	<link href="/select2/select2.css" rel="stylesheet">
    <script type="text/javascript" src="/select2/select2.js"></script>
	<script type="text/javascript" src="/select2/select2_locale_el.js"></script>
    <link rel="stylesheet" href="/select2/select2-small.css">

	<!-- checkbox --> 
	<script src="/bootstrap-checkbox-x/checkbox-x.js" type="text/javascript"></script>
	<link rel="stylesheet" href="/bootstrap-checkbox-x/checkbox-x.css">
	
	<!-- bootstrap date-picker    -->
	<script type="text/javascript" src="/bootstrap-datepicker-master/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="/bootstrap-datepicker-master/bootstrap-datepicker.el.js"></script>   
	<link href="/bootstrap-datepicker-master/datepicker3.css" rel="stylesheet">

    
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
            $('[data-toggle="popover"]').popover();


            $("[id^=openUserDetails]").on("click", function() {
                var user = $(this).attr('id').split('-').pop(-1);
                var user_details_div = $("#userDetails-"+user);

				if(user_details_div.hasClass("out")) {
                    user_details_div.addClass("in");
                    user_details_div.removeClass("out");
                } else if(user_details_div.hasClass("in")) {
                    user_details_div.addClass("out");
                    user_details_div.removeClass("in");
                }else{
                    user_details_div.addClass("in");
                }
            });

            var token = '{!! csrf_token() !!}';

            $("[id^=RowBtnDecline]").on("click", function() {

                var application_id = $(this).attr('id').split('-').pop(-1);
                var r = confirm("{!! trans('application.sureDecline') !!}");

                if(r === true) {
                   $.post( "/applications/decline_application", { _token:token, application_id: application_id} )
                          .done(function(data) {
                                   if(data.status === 'success') {
                                       window.location.replace('/administrators/applications');
                                    }
							});
                 }
            });

            $("[id^=RowBtnAccept]").on("click", function() {

                var application_id = $(this).attr('id').split('-').pop(-1);

                var r = confirm("{!! trans('application.sureAccept') !!}");

                if(r === true) {
                    $.post( "/applications/accept_application", { _token:token,application_id: application_id} )
                        .done(function(data) {

                            if(data.status === 'success') {
                                window.location.replace('/administrators/applications');
                            }
                            else if(data.errors){
                                alert(data.errors.email);
							}
                        });
                }
            });

            $("[id^=RowBtnConfirmationAcceptWithNewInstitution]").on("click", function() {

                var application_id = $(this).attr('id').split('-').pop(-1);

                var r = confirm("{!! trans('application.sureAccept') !!} {!! trans('application.sureAcceptNewInstConf') !!}");

                if(r === true) {
                    $.post( "/applications/accept_application", { _token:token,application_id: application_id} )
                        .done(function(data) {

                            if(data.status === 'success') {
                                window.location.replace('/administrators/applications');
                            }
                            else if(data.errors){
                                alert(data.errors.email);
                            }
                        });
                }
            });

            $(".pagination").addClass("pull-right");

            // Get url parameters
            $.urlParam = function(name){
                var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
                return results[1] || 0;
            };

            // Table limits
            $( "#datatablesChangeDisplayLength" ).val({{ isset($_GET['limit']) ? $_GET['limit'] : 10 }});

            // Table limits
            $( "#datatablesChangeDisplayLength" ).change(function() {
                var value = $( "select option:selected" ).val();
                var limit = value;
                if(value === "-1"){
                    var limit = <?php if(!empty($applications)) { echo $applications->total(); } else { echo 0; } ?>;
                }

                var url = window.location.href;
                var url_pathname = window.location.pathname;
                var current_param = window.location.search.substring(1);
                if(current_param != null){
                    if(url.search("page") > 0){
                        current_param = current_param.replace("page=" + $.urlParam("page"), "");
                        current_param = current_param.replace("&page=" + $.urlParam("page"), "");
                    }
                    current_param = current_param + "&";
                }

                if(url.search("limit") < 0){
                    var params = [{name: "limit", value: limit}];
                    window.location.assign(url_pathname + "?" + current_param + $.param(params));
                }else if(url.search("limit") > 0){
                    current_param = current_param.replace("&limit=" + $.urlParam("limit"), "");
                    current_param = current_param.replace("limit=" + $.urlParam("limit"), "");
                    var params = [{name: "limit", value: limit}];
                    window.location.assign(url_pathname + "?" + current_param + $.param(params));
                }
            });

            // Sort table
            //Default class
            //Default class
            var sortings = ["createdAt", "lastname", "state", "status"];
            var url = window.location.href;
            $.each(sortings, function( index, value ){
                if(url.search("sort_"+value) > 0 && value != "lastname"){
                    $("#sort_"+value).removeClass("sorting");
                    $("#sort_"+value).addClass("sorting"+ $.urlParam("sort_"+value));
                    $("#sort_createdAt").removeClass("sortingasc");
                    $("#sort_createdAt").addClass("sorting");
                }else if(url.search("sort_"+value) > 0 && value == "lastname"){
                    $("#sort_"+value).removeClass("sortingdesc");
                    $("#sort_"+value).addClass("sorting"+ $.urlParam("sort_"+value));
                }
            });

            $("[id^=sort]").on("click", function() {
                var sortings = ["createdAt", "lastname", "state", "status"];
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
                    var params = [{name: "sort_"+col, value: new_var}];
                    window.location.assign(url_pathname + "?" + current_param + $.param(params));
                }
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
		.zero-width {
			display:none;
			width: 0px;
		}
		
		table#userTable th {
			font-size:12px;
			white-space: nowrap !important;
			overflow: hidden !important;
			text-overflow: ellipsis !important;
			-o-text-overflow: ellipsis !important;
		}
		
		table#userTable td{
			padding-left:5px !important;
			padding-right:5px !important;
			white-space: nowrap !important;
			overflow: hidden !important;
			text-overflow: ellipsis !important;
			-o-text-overflow: ellipsis !important;
			width: 10px;
			min-width:10px;
			max-width: 10px;
			} 

		.cellDetails {
			min-width: 30px !important;
		}
		.cellName {
			width: 240px !important;
		}		
		.cellRole {
			width: 160px !important;
		}
		.cellState {
			width: 100px !important;
		}
		.cellOrg {
			width: 170px !important;
		}
		.cellDepart {
			width: 120px !important;
		}
		/*
		.cellType {
			width: 80px !important;
		} */
		.cellStatus {
			width: 100px !important;
		}
		.cellCreationDate {
			width: 100px !important;
		}
		.cellButton {
			padding: 3px !important; 
			width: 90px !important;
			min-width:90px !important;
			max-width:90px !important;
		}

        .table-span{
            white-space:pre-line;
        }

		tfoot {
			display: table-header-group;
		}			
		
		table thead .sorting:before {
			font-family: 'Glyphicons Halflings';
			content: "\e150";
			padding: 0px 2px;
			font-size: 0.8em;
			color: #52b6ec;
		}
		
		table thead .sortingasc:before {
			font-family: 'Glyphicons Halflings';
			content: "\e155";
			padding: 0px 2px;
			font-size: 0.8em;
			color: #52b6ec;
		}
		
		table thead .sortingdesc:before {
			font-family: 'Glyphicons Halflings';
			content: "\e156";
			padding: 0px 2px;
			font-size: 0.8em;
			color: #52b6ec;
		}
		
		table thead .sorting, table thead .sortingasc, table thead .sortingdesc{
			cursor:pointer;
		}	
		
		.user_details{
			cursor:pointer;
		}
		.hiddenRow {
			padding: 0 !important;
		}			
		

	
	</style>
@endsection

@section('users-active')
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
						  @can('view_admins_menu')
                          <li><a href="/administrators">{{trans('users.moderators')}}</a></li>
						  @endcan
						  @can('view_users')
                          <li><a href="/users">{{trans('users.users')}}</a></li>
						  @endcan
						  @can('view_applications')
                          <li class="active"><a href="#">{{trans('users.waitingApproval')}}</a></li>
						  @endcan
                        </ul>
                    </div>
				</div>   
<!-- Tab line -END -->

				<div class="small-gap"></div>
                
				@if ($errors->any())
					<ul class="alert alert-danger" style="margin: 0px 15px 10px 15px">
						<strong>{{trans('users.userNotSaved3')}}</strong>
						@foreach($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				@elseif (session('message'))
					<div class="alert alert-info">
						<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
						{{ session('message') }}
					</div>
				@endif
<!-- DATATABLES START -->
				<div class="row"> <!-- Row with search field and add button - START -->
					<div class="col-md-5 col-sm-12 col-xs-12">
                               <div class="input-group">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-align-justify"></i></span>
                                    <select class="form-control" id="datatablesChangeDisplayLength">
                                        <option value="10" >10</option>
                                        <option value="20">20</option>
                                        <option value="30">30</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="{{ $applications->total() }}">All</option>
                                    </select>
                                </div>
					</div>
				</div> <!-- Row with search field and add button - END -->
                      
				@include('users._applicationsTable', [])

<!-- DATATABLES END -->

            </div><!--/.box-->
        </div><!--/.container-->

		
        
    </section>
@endsection
