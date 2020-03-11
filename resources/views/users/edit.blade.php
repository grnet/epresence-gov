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

	<script type="text/javascript">

        $(document).ready(function () {

            let new_dep_container = $("#NewDepContainer");
			let department_select_field = $("#FieldUserDepart");
			let new_custom_department_field = $("#FieldUserDepartNew");
			department_select_field.select2({placeholder: "{!!trans('site.selectDepartment')!!}"});
			@if($auth_user->hasRole('SuperAdmin'))
			let organization_select_field = $("#FieldUserOrg");
			organization_select_field.select2({placeholder: "{!!trans('site.selectInstitution')!!}"});
			organization_select_field.select2({
				allowClear: true,
				placeholder: "{!!trans('users.selectInstitutionRequired')!!}"
			}).on("change", function () {
				update_ia_inst_selection_ui(true);
			});
			update_ia_inst_selection_ui(false);
			function update_ia_inst_selection_ui(load_departments){
				if(organization_select_field.val() > 0) {
					if(load_departments){
						department_select_field.select2("data", null, {allowClear: true}).load("/institutions/departments/" + organization_select_field.val());
					}
				}
				else {
					 function_clear_inst_admin_selections();
				}
			}
			function function_clear_inst_admin_selections(){
				organization_select_field.select2("data", null, {allowClear: true});
				department_select_field.html('<option value=""></option>');
				department_select_field.select2("data", null, {allowClear: true});
				department_select_field.select2({
					allowClear: true,
					placeholder: "{!!trans('users.selectInstitutionFirst')!!}",
				});
			}
			@endif
			// UserDepart On change
            department_select_field.on("change", function () {
                if (department_select_field.val() === "other") {
                    new_dep_container.show();
                    new_custom_department_field.show();
                } else if (department_select_field.val() !== "other" && department_select_field.val() > 0) {
                    new_dep_container.hide();
                    new_custom_department_field.hide();
                }
                else if (department_select_field.val() === "") {
                    new_dep_container.hide();
                    new_custom_department_field.hide();
                }
            }).trigger("change");
			$('[data-toggle="tooltip"]').tooltip();
            $("#FieldUserStatusAlert").hide();
            $("#FieldUserRole").select2();
            // Activate user account
            $('#FieldUserStatus').on("change", function(evt) {
				let  CurrentFieldStatus = $('#FieldUserStatus').val();
                if (parseInt(CurrentFieldStatus) === 0) {
                    $("#FieldUserStatusAlert").hide();
                }
                else if (parseInt(CurrentFieldStatus) === 1){
                    $("#FieldUserStatusAlert").show();
                    $("#FieldUserStatusMessage").text("{!!trans('users.activationSelected')!!}: ");
                }
            });
		});
	</script>
@endsection
@section('extra-css')
<style>
		.container
			{
				min-width: 550px !important;
				text-align: left;
			}
		.zero-width {
			display:none;
			width: 0;
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
		.newdep{
			margin-top: 5px !important;
		}
/* CLASSES FOR USERS DATATABLE END */


	
	</style>
@endsection

@section('users-active')
	class = "active"
@endsection

@section('content')
        <section id="Users">
        <div class="container">
            <div class="box first" style="margin-top:100px">
			@if ($errors->any())
				<ul class="alert alert-danger" style="margin: 0 15px 10px 15px">
					<strong>{{trans('users.changesNotSaved')}}</strong>
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
				{!! Form::model($user, array('url' => ['users/' . $user->id.'/'.$user->state], 'method' => 'PATCH', 'class' => 'form-horizontal', 'id' => 'OrgForm', 'role' => 'form', 'files' => true)) !!}
				@include('users.edit_sso_user_form')
				{!! Form::close() !!}
			</div>                        
		</div><!--/.box-->
<!-- Form Details -END -->
@endsection
