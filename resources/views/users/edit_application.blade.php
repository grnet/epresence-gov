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
		$(document).ready(function() {
			
			$('.summernote').summernote({
				lang: 'el-GR' 
			});
			
			$("#FieldApplicationStatus").select2({placeholder: "-- {!!trans('users.select')!!} --"});
			
			$("#FieldUserRole").select2();
			
			$("#FieldUserDepart").select2();
			
			$("#FieldUserOrg").select2();
			
			// Default inputs
			if($("#FieldUserOrg").val() == "other" && $("#FieldUserRole").val() == "InstitutionAdministrator"){
				$("#UserOrgNew").hide();
				$("#UserDepartNew").hide();
				$("#UserDepart").hide();
			}else if($("#FieldUserOrg").val() != "other" && $("#FieldUserRole").val() == "InstitutionAdministrator"){
				$("#UserOrgNew").hide();
				$("#UserDepartNew").hide();
				$("#UserDepart").hide();
				$("#FieldUserDepart").hide();
			}else if($("#FieldUserOrg").val() == "other" && $("#FieldUserRole").val() != "InstitutionAdministrator"){
				$("#UserOrgNew").show();
				$("#UserDepartNew").show();
				$("#UserDepart").show();
				$("#FieldUserDepart").show();
			}else if($("#FieldUserOrg").val() != "other" && $("#FieldUserDepart").val() == "other" && $("#FieldUserRole").val() != "InstitutionAdministrator"){
				$("#UserOrgNew").hide();
				$("#UserDepartNew").show();
				$("#FieldUserDepartNew").show();
				$("#UserDepart").show();
				$("#FieldUserDepart").show();
			}
			else if($("#FieldUserOrg").val() != "other" && $("#FieldUserDepart").val() != "other" && $("#FieldUserRole").val() != "InstitutionAdministrator"){
				$("#UserOrgNew").hide();
				$("#UserDepartNew").hide();
				$("#FieldUserDepartNew").hide();
				$("#UserDepart").show();
				$("#FieldUserDepart").show();
			}

			// FieldUserOrg On change
			$("#FieldUserOrg").change(function() {
				var user_dep = <?php echo $user->departments()->first()->id ?>;
				if ($("#FieldUserOrg").val() == "other" && $("#FieldUserRole").val() != "InstitutionAdministrator"){
					$("#UserOrgNew").show();
					$("#UserDepartNew").show();
					$("#UserDepart").hide();
				}else if($("#FieldUserOrg").val() != "other" && $("#FieldUserOrg").val() != <?php echo $user->institutions()->first()->id ?> && $("#FieldUserRole").val() != "InstitutionAdministrator") {
					$("#UserOrgNew").hide();
					$("#UserDepartNew").hide();
					$("#UserDepart").show();
					$("#FieldUserDepart").select2("data", null).load("/institutions/departments/" + $("#FieldUserOrg").val());
				}else if($("#FieldUserOrg").val() == <?php echo $user->institutions()->first()->id ?> && $("#FieldUserRole").val() != "InstitutionAdministrator"){
					if(user_dep == <?php echo $user->institutions()->first()->otherDepartment()->id ?>){
						$("#UserOrgNew").hide();
						$("#UserDepartNew").show();
						$("#FieldUserDepartNew").show();
						$("#UserDepart").show();
					}else{
						$("#UserOrgNew").hide();
						$("#UserDepartNew").hide();
						$("#UserDepart").show();
						$("#FieldUserDepart").select2("data", {id: <?php echo $user->departments()->first()->id; ?>, text: '<?php echo $user->departments()->first()->title; ?>'}).load("/institutions/departments/" + $("#FieldUserOrg").val());
					}
				}else if($("#FieldUserOrg").val() == "other" && $("#FieldUserRole").val() == "InstitutionAdministrator") {
					$("#UserOrgNew").show();
					$("#UserDepartNew").hide();
					$("#UserDepart").hide();
				}else if($("#FieldUserOrg").val() != "other" && $("#FieldUserRole").val() == "InstitutionAdministrator"){
					$("#UserOrgNew").hide();
					$("#UserDepartNew").hide();
					$("#UserDepart").hide();
					$("#FieldUserDepart").hide();
				}
			}).trigger("change");
			

			// UserDepart On change
			$("#FieldUserDepart").on("change", function() {
				if ($("#FieldUserDepart").val() == "other"){	
					$("#UserDepartNew").show();
					$("#FieldUserDepartNew").show();
				}else if($("#FieldUserDepart").val() != "other" && $("#FieldUserDepart").val() > 0) {
					$("#UserDepartNew").hide();
					$("#FieldUserDepartNew").hide();
				}
				else if($("#FieldUserDepart").val() == ""){
					$("#UserDepartNew").hide();
					$("#FieldUserDepartNew").hide();
				}
			}).trigger("change");
			
			
			// FieldUserRole On change
			$("#FieldUserRole").change(function() {
				var user_dep = <?php echo $user->departments()->first()->id ?>;
				if ($("#FieldUserOrg").val() == "other" && $("#FieldUserRole").val() != "InstitutionAdministrator"){
					$("#UserOrgNew").show();
					$("#UserDepartNew").show();
					$("#UserDepart").hide();
				}else if($("#FieldUserOrg").val() != "other" && $("#FieldUserOrg").val() != <?php echo $user->institutions()->first()->id ?> && $("#FieldUserRole").val() != "InstitutionAdministrator") {
					$("#UserOrgNew").hide();
					$("#UserDepartNew").hide();
					$("#UserDepart").show();
					$("#FieldUserDepart").select2("data", null).load("/institutions/departments/" + $("#FieldUserOrg").val());
				}else if($("#FieldUserOrg").val() == <?php echo $user->institutions()->first()->id ?> && $("#FieldUserRole").val() != "InstitutionAdministrator"){
					if(user_dep == <?php echo $user->institutions()->first()->otherDepartment()->id ?>){
						$("#UserOrgNew").hide();
						$("#UserDepartNew").show();
						$("#FieldUserDepartNew").show();
						$("#UserDepart").show();
					}else{
						$("#UserOrgNew").hide();
						$("#UserDepartNew").hide();
						$("#UserDepart").show();
						$("#FieldUserDepart").select2("data", {id: <?php echo $user->departments()->first()->id; ?>, text: '<?php echo $user->departments()->first()->title; ?>'}).load("/institutions/departments/" + $("#FieldUserOrg").val());
					}
				}else if($("#FieldUserOrg").val() == "other" && $("#FieldUserRole").val() == "InstitutionAdministrator") {
					$("#UserOrgNew").show();
					$("#UserDepartNew").hide();
					$("#UserDepart").hide();
				}else if($("#FieldUserOrg").val() != "other" && $("#FieldUserRole").val() == "InstitutionAdministrator"){
					$("#UserOrgNew").hide();
					$("#UserDepartNew").hide();
					$("#UserDepart").hide();
					$("#FieldUserDepart").hide();
				}
			}).trigger("change");
			
		});
		
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
/* CLASSES FOR USERS DATATABLE END */

		.user-label{
			text-align: right;
			padding-top: 7px;
			margin-top: 0;
			margin-bottom: 0;
			display: inline-block;
			font-weight: bold;
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
				<div class="row">
                	<div class="col-xs-12">
                		<h4>{{trans('users.applicationUnderApproval')}}</h4>
						<hr>
                    </div>
                </div>
				
              	<div class="row">
				@if ($errors->any())
					<ul class="alert alert-danger" style="margin: 0px 15px 10px 15px">
						<strong>{trans('users.changesNotSaved')}}</strong>
						@foreach($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				@endif
				{!! Form::model($user, array('url' => ['users/' . $user->id], 'method' => 'PATCH', 'class' => 'form-horizontal', 'id' => 'OrgForm', 'role' => 'form')) !!}
					@include('users._form', ['label' => '2', 'input' => '4', 'state' => $user->state, 'status' => $user->status, 'role' => $user->roles->first()->name, 'institution' => $user->institutions->first(), 'institution_id' => $user->institutions->first()->id,  'department' => $user->departments->first(), 'department_id' => $user->departments->first()->id, 'customValues' => $user->customValues(), 'application' => $user->application, 'from_page' => 'moderatorApplication', 'action' => 'edit', 'submitBtn' => trans('users.saveChanges')])  
				{!! Form::close() !!}
				</div>
			</div>                        
		</div><!--/.box-->
<!-- Form Details -END -->
@endsection
