@if(is_null(Session::get('previous_url')))
	{{ Session::put('previous_url', URL::previous()) }}
@endif

@extends('app')

@section('header-javascript')
	<script type="text/javascript" src="datatables/jquery.dataTables.js"></script>
    <script type="text/javascript" src="datatables/dataTables.bootstrap.js"></script>
	<link href="datatables/dataTables.bootstrap.css" rel="stylesheet">
	
	<link href="select2/select2.css" rel="stylesheet">
    <script type="text/javascript" src="/select2/select2.js"></script>
	<script type="text/javascript" src="/select2/select2_locale_el.js"></script>
    <link rel="stylesheet" href="/select2/select2-small.css">

    
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->    
    


   	<script type="text/javascript">
		$(document).ready(function() {
				
		$('[data-toggle="tooltip"]').tooltip();
		
		// Advanced search
		$("#searchDepartment").select2({
			containerCssClass: "select2-container-sm",
			dropdownCssClass: "tpx-select2-drop",
			allowClear: true
		});
			
		$("#searchDepartment").select2({allowClear: true, placeholder: "{!!trans('deptinst.selectInstitution')!!}"});
			$("[id^=openDepartmentDetails]").on("click", function() {
				var user = $(this).attr('id').split('-').pop(-1);
				if($("#departmentDetails-"+user).hasClass("out")) {
					$("#departmentDetails-"+user).addClass("in");
					$("#departmentDetails-"+user).removeClass("out");
				} else if($("#departmentDetails-"+user).hasClass("in")) {
					$("#departmentDetails-"+user).addClass("out");
					$("#departmentDetails-"+user).removeClass("in");
				}else{
					$("#departmentDetails-"+user).addClass("in");
				}
			});

			$('[id^=RowBtnDelete]').click(function() {
				var row = $(this).closest('tr');
				var nRow = row[0];
				var department = $(this).attr('id').split('-').pop(-1);
				var r = confirm("{!!trans('deptinst.qDeleteDepartment')!!}");
					if (r === true) {
						$.get( "/departments/delete/"+department )
						.done(function(data) {
							if(data.status === 'error'){
								alert(data.data);
							}else if(data.status === 'success'){
								nRow.remove();
								return false;
							}
						});
					}
			});
			
			$(".pagination").addClass( "pull-right" );
			
			// Get url parameters
			$.urlParam = function(name){
				var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
				return results[1] || 0;
			}
			
			$( "#datatablesChangeDisplayLength" ).val({{ isset($_GET['limit']) ? $_GET['limit'] : 10 }});
			
			// Table limits
			$( "#datatablesChangeDisplayLength" ).change(function() {
				var value = $( "select option:selected" ).val();
				var limit = value;
				if(value === "-1"){
					var limit = <?php if(!empty($departments)) { echo $departments->total(); } else { echo 0; } ?>;
				}
				
				var url = window.location.href;
				var url_pathname = window.location.pathname;
				var current_param = window.location.search.substring(1);
				if(current_param != null){
					if(url.search("page") > 0){
						current_param = current_param.replace("&page=" + $.urlParam("page"), "");
						current_param = current_param.replace("page=" + $.urlParam("page"), "");
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
			var sortings = ["title", "url", "contactName", "contactEmail", "contactPhone"];
			var url = window.location.href;
			$.each(sortings, function( index, value ){
				if(url.search("sort_"+value) > 0 && value != "title"){
					$("#sort_"+value).removeClass("sorting");
					$("#sort_"+value).addClass("sorting"+ $.urlParam("sort_"+value));
					$("#sort_title").removeClass("sortingasc");
					$("#sort_title").addClass("sorting");
				}else if(url.search("sort_"+value) > 0 && value == "title"){
					$("#sort_"+value).removeClass("sortingasc");
					$("#sort_"+value).addClass("sorting"+ $.urlParam("sort_"+value));
				}
			});
			
			$("[id^=sort]").on("click", function() {
				var sortings = ["title", "url", "contactName", "contactEmail", "contactPhone"];
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


			//  Button Εισαγωγής Νέου Τμήματος
	  		$( "#NewDep" ).click(function() {
				$('[id^=Field]').prop('disabled', false);
				$('[id^=SubmitBtn]').hide();
				$("#SubmitBtnNew").show();
				$("#DepModalLabel").text("{{trans('deptinst.addNewDept')}}");
				$("#slug").val("");
				$("#title").val("");
				$("#url").val("");
				$("#contact_name").val("");
				$("#FieldDepΑminΝame").val("");
				$("#contact_email").val("");
				$("#contact_phone").val("");
				$("#DepModalMessage").hide();				
  				$("#DepModal").modal("show");
			});
			
			// Close Button στο modal
			$("#ModalButtonClose").click(function() {
				$("#DepModal").modal("hide");
			});
			
        });
        </script>
@endsection
@section('extra-css')
<style>
		.container{
			min-width: 400px !important;
		}
		.zero-width {
			display:none;
			width: 0px;
		}
		.cellDetails {
			min-width: 30px !important;
		}
		.cellName {
			width: 240px !important;
		}
		.cellButton {
			padding: 3px !important; 
			width: 80px !important;
			min-width:80px !important;
			max-width:80px !important;
		}
		table#departmentTable th {
			font-size:12px;
			white-space: nowrap !important;
			overflow: hidden !important;
			text-overflow: ellipsis !important;
			-o-text-overflow: ellipsis !important;
		}	
		
		table#departmentTable td{
			white-space: nowrap !important;
			overflow: hidden !important;
			text-overflow: ellipsis !important;
			-o-text-overflow: ellipsis !important;
			width: 10px;
			min-width:10px;
			max-width: 10px;
			}
		table thead .sorting:before {
			font-family: 'Glyphicons Halflings';
			content: "\e150";
			padding: 0 2px;
			font-size: 0.8em;
			color: #52b6ec;
		}
		
		table thead .sortingasc:before {
			font-family: 'Glyphicons Halflings';
			content: "\e155";
			padding: 0 2px;
			font-size: 0.8em;
			color: #52b6ec;
		}
		
		table thead .sortingdesc:before {
			font-family: 'Glyphicons Halflings';
			content: "\e156";
			padding: 0 2px;
			font-size: 0.8em;
			color: #52b6ec;
		}
		
		table thead .sorting, table thead .sortingasc, table thead .sortingdesc{
			cursor:pointer;
		} 
		
		.department_details{
			cursor:pointer;
		}
		.hiddenRow {
			padding: 0 !important;
		}
			
	</style>
@endsection

@section('institutions-active')
	class = "active"
@endsection

@section('content')

<section id="Dep">
        <div class="container">
            <div class="box first" style="margin-top:100px">
				<h4><a href="{{ Session::get('previous_url') }}"><span class="glyphicon glyphicon-arrow-left" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="{{trans('deptinst.backInstList')}}"></span></a> {{trans('deptinst.institutionDepts')}}: {{ $departments->first()->institution->title }}</h4>
				<hr/>
				<div class="small-gap"></div>
				@if (session('storesSuccessfully'))
					<div class="alert alert-info" role="alert" style="margin: 0px 15px 10px 15px">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
						{!! session('storesSuccessfully') !!}
					</div>
				@endif
				@if ($errors->any())
					<ul class="alert alert-danger" style="margin: 0px 15px 10px 15px">
						<strong>{{trans('deptinst.departmentNotSaved')}}</strong>
						@foreach($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
						</ul>
				@endif
<!-- DATATABLES START -->
				<div class="row"> <!-- Row with search field and add button - START -->
					<div class="col-xs-12">
                        <span class="pull-left" style="width:110px">
                               <div class="input-group">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-align-justify"></i></span>
                                    <select class="form-control" id="datatablesChangeDisplayLength">
                                        <option value="10" >10</option>
                                        <option value="20">20</option>
                                        <option value="30">30</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="{{ $departments->total() }}">All</option>
                                    </select>
                                </div>
						</span>
						<!--<span class="pull-left" >
                            <div class="input-group" style="width:200px">
                                 <a class="btn btn-primary" role="button" data-toggle="collapse" href="#collapseAdvancedDearch" aria-expanded="false" aria-controls="collapseAdvancedDearch" style="margin-left:5px;">Αναζήτηση <span class="glyphicon glyphicon-search" aria-hidden="true"></span></a>
                            </div>
                        </span>-->
                        <span class="pull-right">
                            <button type="button" class="btn btn-success" data-toggle="modal" id="NewDep">
                                <small><span class="glyphicon glyphicon-plus-sign"></span> {{trans('deptinst.newDept')}}</small>
                             </button>
                        </span>
					</div>
				</div> <!-- Row with search field and add button - END -->
                @include('departments._departmentTable', [])
<!-- DATATABLES END -->
            </div><!--/.box-->
        </div><!--/.container-->
	<!-- MODAL start--->
		<div class="modal fade" id="DepModal" tabindex="-1" role="dialog" aria-labelledby="OrgModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="OrgModalLabel">Τμήμα</h4>
					</div> <!-- .modal-header -->
					<div class="modal-body">
						{!! Form::open(array('url' => 'departments', 'method' => 'post', 'class' => 'form-horizontal')) !!}
                        
						<div class="form-group">
								{!! Form::label('institution_id', trans('deptinst.institution').':', ['class' => 'control-label col-sm-4 ']) !!}
                                <div class="col-sm-8 form-control-static">
									{{ $departments->first()->institution->title}}
                                </div>
                            </div>
							{!! Form::hidden('institution_id', $departments->first()->institution_id) !!}
						<div class="form-group">
								{!! Form::label('title', trans('deptinst.description').':', ['class' => 'control-label col-sm-4 ']) !!}
                                <div class="col-sm-8">
									{!! Form::text('title', null, ['class' => 'form-control', 'id' => 'title', 'placeholder' => trans('deptinst.avoidWordDept')]) !!}
                                </div>
                            </div>
                       </div> <!-- .modal-body -->
					
					<div class="modal-footer">
						<div class="form-group">
							{!! Form::submit(trans('deptinst.saveNewDept'), ['class' => 'btn btn-success']) !!}
							<button type="button" id="ModalButtonClose" class="btn btn-default">{{trans('deptinst.cancel')}}</button>
						</div>
					</div>
					</div> <!-- .modal-footer -->
                  {!! Form::close() !!}             
				</div> <!-- .modal-content -->
			</div> <!-- .modal-dialog -->
		</div> <!-- .modal -->				
<!-- modal insit end---> 				
<!-- modal insit end --> 
    </section><!--/#Dep-->       
@endsection
