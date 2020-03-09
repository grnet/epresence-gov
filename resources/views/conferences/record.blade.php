@extends('app')

@section('header-javascript')
	<script type="text/javascript" src="/datatables/jquery.dataTables.js"></script>
    <script type="text/javascript" src="/datatables/dataTables.bootstrap.js"></script>
	<script type="text/javascript" src="/datatables/date-eu.js"></script>

	<link href="/datatables/dataTables.bootstrap.css" rel="stylesheet">
	
	<!-- bootstrap date-picker    -->
	<script type="text/javascript" src="/bootstrap-datepicker-master/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="/bootstrap-datepicker-master/bootstrap-datepicker.el.js"></script>   
	<link href="/bootstrap-datepicker-master/datepicker3.css" rel="stylesheet">

    
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->    
    


   	<script type="text/javascript">
		$(document).ready(function() {		
		



		
  		  		
// ΕΝΕΡΓΟΠΟΙΗΣΗ TOOLTIP

  		$('[data-toggle="tooltip"]').tooltip();

// ΣΥΝΑΡΤΗΣΕΙΣ ΣΧΕΤΙΚΕΣ ΜΕ DATATABLES

			//  Ορισμός Πίνακα DataTable - {"bVisible": false} για να κρύψουμε πχ rec-no
			$("#example").dataTable( {
				"oLanguage": {
						"sZeroRecords": "{{trans('conferences.noConferencesFound')}}",
						"sInfoFiltered": "{{trans('conferences.fromNoConferences')}}",
						},
				"aoColumns": [
					{ "sClass": "cellID"},
					{ "sClass": "cellDesc"},
					{ "sClass": "cellStartDate", "sType": "date-eu" },
					{ "sClass": "cellStartTime"},
					{ "sClass": "cellEndTime"},
					{ "sClass": "cellAdmin hidden-xs"},
					{ "sClass": "cellUHV hidden-sm hidden-xs", "sType": "numeric"},					
					{ "sClass": "cellButton", "bSortable": false }
				]				
			} );



			var oTable = $("#example").dataTable();	
			function changeDisplayLength(oTable, iDisplayLength) {
    			var oSettings = oTable.fnSettings();
    			oSettings._iDisplayLength = iDisplayLength; 
    			oTable.fnDraw();
			}
													
    		$("#datatablesChangeDisplayLength").change(function() {
        		changeDisplayLength(oTable, +($(this).val()));
    		});

  			$("#datatablesSearchTextField").keyup(function(){
      			oTable.fnFilter( $(this).val() );
			})
																
			
			// φιλτρα σε columns
			$('#selectColID').on( 'keyup change', function () {
				oTable.fnFilter( $(this).val(), 0 );
			});
			
			$('#selectColDesc').on( 'keyup change', function () {
				oTable.fnFilter( $(this).val(), 1 );
			});
			
			$('#selectColStartDate').on('keyup change', function(){
				var selected = $(this).val();
				oTable.fnFilter( selected, 2 );
			  });
			  
			 $('.datepicker').datepicker({
				format: "dd-mm-yyyy",
				todayBtn: "linked",
				language: "el",
				autoclose: true,
				todayHighlight: true
			});
			  
			$('#selectColStartTime').on( 'keyup change', function () {
				oTable.fnFilter( $(this).val(), 3 );
			});
			
			$('#selectColEndTime').on( 'keyup change', function () {
				oTable.fnFilter( $(this).val(), 4 );
			});
						
			$('#selectColAdmin').on( 'keyup change', function () {
				oTable.fnFilter( $(this).val(), 5 );
			});
						

			  
	  		$( "#ClearFilter" ).click(function() {
				$("[id^=selectCol], input[type=text]").val(null);
				var oSettings = oTable.fnSettings();
					for(iCol = 0; iCol < oSettings.aoPreSearchCols.length; iCol++) {
						oSettings.aoPreSearchCols[ iCol ].sSearch = '';
					}
				oTable.fnDraw();
			});		
			
			
			$("#NewTele").click(function() {
				window.location.href = "/conferences/create";	
			});				  			  
  
  
	  		//  Ανάκτηση στοιχείων από τον πίνακα			
			oTable.$('tr').click( function () {
			var data = oTable.fnGetData( this );
				$("#FieldDesc").val(data[01]);
				$("#FieldStartDate").val(data[02]);
				$("#FieldStartTime").val(data[03]);
				$("#FieldAdmin").val(data[05]);
  			} );		
			  

			<!-- Delete Button στα Rows --!>
			$('[id^=RowBtnDelete]').click(function() {
				var row = $(this).closest('tr');
				var nRow = row[0];
				var conference = $(this).attr('id').split('-').pop(-1);
				var r = confirm("{{trans('conferences.confirmDeleteConference')}}");
					if (r == true) {
						var xhttp = new XMLHttpRequest();
						xhttp.open("GET", "/conferences/delete/"+conference, true);
						xhttp.send();
						oTable.fnDeleteRow(nRow);
					}
			});			
			
			



// ΣΥΝΑΡΤΗΣΕΙΣ ΣΧΕΤΙΚΕΣ ΜΕ ΤΗΝ ΑΠΟΣΤΟΛΗ EMAIL

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
			
			
			
        } );
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
		table#example td {
			white-space: nowrap !important;
			overflow: hidden !important;
			text-overflow: ellipsis !important;
			-o-text-overflow: ellipsis !important;
			width: 10px;
			min-width:10px;
			max-width: 10px;
			} 

		.cellID {
			width: 40px !important;
		}
		.cellDesc {
			width: 340px !important;
		}
		.cellStartDate {
			width: 110px !important;
		}
		.cellStartTime {
			width: 80px !important;
		}
		.cellEndTime {
			width: 80px !important;
		}
		.cellAdmin {
			width: 260px !important;
		}
		.cellUHV {
			width: 60px !important;
		}
		.cellButton {
			padding: 3px !important; 
			width: 90px !important;
			min-width:90px !important;
			max-width:90px !important;
		}
		tfoot {
			display: table-header-group;
		}			
		

	
	</style>
@endsection

@section('conference-active')
	class = "active"
@endsection

@section('content')
	<section id="vroom">
        <div class="container">
            <div class="box first" style="margin-top:100px">
                
<!-- Tab line -START -->
				<div class="row">
					<div class="col-sm-12">            
                        <ul class="nav nav-tabs">
                          <li ><a href="/conferences">{{trans('conferences.conferences')}}</a></li>
                          <li><a href="/calendar">{{trans('conferences.calendar')}}</a></li>
                          <li><a href="/conferences/ongoing">{{trans('conferences.ongoing')}}</a></li>
                          <li  class="active"><a href="/conferences/record">{{trans('conferences.history')}}</a></li>
						  <li><a href="/conferences/settings">{{trans('conferences.settings')}}</a></li>

                        </ul>
                    </div>
				</div>   
<!-- Tab line -END -->

				<div class="small-gap"></div>
                
<!-- DATATABLES START -->

				<div class="row"> <!-- Row with search field and add button - START -->
					<div class="col-md-5 col-sm-12 col-xs-12">
                        <span class="pull-left" style="width:110px">
                               <div class="input-group">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-align-justify"></i></span>
                                    <select class="form-control" id="datatablesChangeDisplayLength">
                                        <option value="10" >10</option>
                                        <option value="20">20</option>
                                        <option value="30">30</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="-1">All</option>
                                    </select>
                                </div>
						</span>
                        <span class="pull-left" >
                            <div class="input-group" style="width:200px">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span>
                                <input type="text" class="form-control" style="width:200px" id="datatablesSearchTextField">
                            </div>
                        </span>
					</div>
					<div class="col-md-7 col-sm-12 col-xs-12" style="text-align:right">
					</div>					
				</div> <!-- Row with search field and add button - END -->   
                
    
                <table style="margin-top:10px; width:100%" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped table-bordered" id="example">
                    <thead>
                     <tr>
                            <th >{{trans('conferences.id')}}</th>
                            <th >{{trans('conferences.title')}}</th>
                            <th >{{trans('conferences.date')}}</th>
							<th >{{trans('conferences.start')}}</th>
                            <th >{{trans('conferences.end')}}</th>
                            <th >{{trans('conferences.moderator')}}</th>
                            <th >{{trans('conferences.participants')}}</th>
                            <th ></th>
                      </tr>

                    </thead>
                    <tfoot style="background-color: #b0b0b0">
                        <tr>
                            <th> <input class="form-control input-sm" id="selectColID" type="text" style="width:100%"></th>                          
                            <th> <input class="form-control input-sm" id="selectColDesc" type="text" style="width:100%"></th>                          
							<th> <input class="form-control input-sm date datepicker" id="selectColStartDate" type="text" style="width:100%"></th>                           
							<th><input class="form-control input-sm" id="selectColStartTime" type="text" style="width:100%"></th>
                            <th><input class="form-control input-sm" id="selectColEndTime" type="text" style="width:100%"></th>
                            <th><input class="form-control input-sm" id="selectColAdmin" type="text" style="width:100%"></th> 
                            <th></th>
                            <th>	
                            	<button id="ClearFilter" type="button" class="btn btn-sm" style="width:100%; border:0px; margin-top:6px" data-toggle="tooltip" data-placement="top" title="{{trans('conferences.resetFilters')}}"><span class="glyphicon glyphicon-filter"></span><strong>&times;</strong></button>
                            </th>
                        </tr>
                    </tfoot>
                    <tbody>
                        
						 @foreach ($conferences as $conference)
							<tr>
								<td>{{ $conference->id }}</td>							
								<td>{{ $conference->title }}</td>							
								<td>{{ $conference->getDate($conference->start) }}</td>							
								<td>{{ $conference->getTime($conference->start) }}</td>							
								<td>{{ $conference->getTime($conference->end) }}</td>							
								<td>{{ $conference->user->firstname }} {{ $conference->user->lastname }}</td>							
								<td>{{ $conference->users_no }}</td>							
								<td class="center">
								<a href="/conferences/{{ $conference->id }}/details"><button id="RowBtnEdit-{{ $conference->id }}" type="button" class="btn btn-default btn-sm m-right btn-border"><span class="glyphicon glyphicon-search"></span></button></a>
							</tr>
						@endforeach   
                                      
                    </tbody>
                </table>       


<!-- DATATABLES END -->

            </div><!--/.box-->
        </div><!--/.container-->
        
        

<!-- MODAL start--->
		<div class="modal fade" id="TeleModal" tabindex="-1" role="dialog" aria-labelledby="TeleModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="TeleModalLabel">{{trans('conferences.deleteConference')}}</h4>
					</div> <!-- .modal-header -->
					<div class="modal-body">
					<div class="alert alert-info" role="alert" id="TeleModalMessage">{{trans('conferences.conferenceDeleted')}} </div>

                        <form id="OrgForm" class="form-horizontal" role="form">
                        
                            <div class="form-group">
                                <label for="FieldDesc" class="control-label col-sm-3">{{trans('conferences.description')}}:</label>
                                <div class="col-sm-9">
                                    <input class="form-control" id="FieldDesc"  />
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="FieldStartDate" class="control-label col-sm-3">{{trans('conferences.date')}}:</label>
                                <div class="col-sm-9">
                                    <input class="form-control" id="FieldStartDate" style="width:90px"/>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="FieldStartTime" class="control-label col-sm-3">{{trans('conferences.startTime')}}:</label>
                                <div class="col-sm-9">
                                    <input class="form-control" id="FieldStartTime" style="width:90px"/>
                                </div>
                            </div>                       
                        
                            <div class="form-group">
                                <label for="FieldAdmin" class="control-label col-sm-3">{{trans('conferences.moderator')}}:</label>
                                <div class="col-sm-9">
                                    <input class="form-control" id="FieldAdmin"/>
                                </div>
                            </div>
                        </form>                  
					</div> <!-- .modal-body -->
	
					<div class="modal-footer">
                        <div class="pull-left" style="padding-top:5px">{{trans('conferences.areYouSure')}}</div>
                        <button type="button" id="ModalButtonDelete" class="btn btn-danger">{{trans('conferences.delete')}}</button>                           
						<button type="button" id="ModalButtonClose" class="btn btn-default" data-dismiss="modal">{{trans('conferences.cancel')}}</button>
					</div> <!-- .modal-footer -->
				</div> <!-- .modal-content -->
			</div> <!-- .modal-dialog -->
		</div> <!-- .modal -->				
<!-- modal insit end--->       


       

           
        

       

<!-- MODAL SendInviation start--->
        <div class="modal fade" id="InvEmailModal" tabindex="-1" role="dialog" aria-labelledby="InvEmailLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title" id="InvEmailLabel">Αποστολή Email</h4>
                            </div> <!-- .modal-header -->
                            <div class="modal-body">
                            <div class="alert alert-info" role="alert" id="InvEmailMessage" >Η Αποστολή πραγματοποιηθηκε με επιτυχία </div>
        
                                <form id="InvEmailForm" class="form-horizontal" role="form">
        
                                    <div class="form-group">
                                        <label for="FieldInvEmailFrom" class="control-label col-sm-1" style="text-align:left">Από:</label>
                                        <div class="col-sm-11">
                                            <input class="form-control" id="FieldInvEmailFrom" placeholder="Email αποστολέα (αρχική τιμή από τις ρυθμίσεις)" />
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label for="FieldInvEmailSubject" class="control-label col-sm-1" style="text-align:left">Θέμα:</label>
                                        <div class="col-sm-11">
                                            <input class="form-control" id="FieldInvEmailSubject" placeholder="Θέμα (αρχική τιμή  από τις ρυθμίσεις)"  />
                                        </div>
                                    </div>
        
                                    <div class="form-group" >
                            			<label for="FieldInvEmailTo" class="control-label col-sm-1" style="text-align:left">Προς:</label>
                            			<div class="col-sm-11 form-control-static">
                            				Σε όλους τους συμμετέχοντες των τηλεδιασκέψεις της λίστας <br>(όπως αυτή έχει διαμορφωθεί και εμφανίζεται σύμφωνα με τα φίλτρα αναζήτησης) 
                            			</div>
                        			</div>                         
                                                                  
                    				<div class="form-group" >
                                    	<label for="FieldInvMessage" class="control-label col-lg-12" style="text-align:left">Μήνυμα:</label>
                                        	<div class="col-lg-12">
                                            	<div id="FieldInvMessage">Μήνυμα (αρχική τιμή  από τις ρυθμίσεις)</div>
                                             </div>
                                  	</div>               
                                      
                                </form>      
							</div>
            
                            <div class="modal-footer" style="margin-top:0px;">
                                <button  id="InvEmailSubmitBtn" class="btn btn-primary">Αποστολή Email</button>                            
                                <button type="button" id="InvEmailModalButtonClose" class="btn btn-default" data-dismiss="modal">Ακύρωση</button>
        
        
                            </div> <!-- .modal-footer -->
                        </div> <!-- .modal-content -->
                    </div> <!-- .modal-dialog -->
                </div>	
<!-- modal InvEmail end--->               
		
    </section>
@endsection
