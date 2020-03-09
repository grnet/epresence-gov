				<div class="col-md-12 col-sm-12 col-xs-12 collapse" id="collapseAdvancedDearch">
					<div class="small-gap"></div>
					<div class="well">
						<h4>Advanced Search <span class="glyphicon glyphicon-search" aria-hidden="true"></span></h4>
						<hr/>
					
						{!! Form::open(array('url' => Request::fullUrl(), 'method' => 'get', 'class' => 'form-horizontal', 'id' => 'CoordOrgForm', 'role' => 'form')) !!}
						
						<div class="row">
							<div class="col-sm-4">
								{!! Form::select('id', ['' => ''] + App\Institution::orderBy('title')->pluck('title', 'id')->toArray(), Input::get('id'), ['id' => 'searchInstitution', 'style' => 'width: 100%'])!!}
							</div>
							<div class="col-sm-4">
								{!! Form::text('shibboleth_domain', Input::get('shibboleth_domain'), ['class' => 'form-control', 'placeholder' => 'Shibboleth domain', 'id' => 'searchShibbolethDomain']) !!}
							</div>
						</div>
						
						<div class="small-gap"></div>
						
						<div class="row">
							<div class="col-sm-4">
								{!! Form::text('moderator_firstname', Input::get('moderator_firstname'), ['class' => 'form-control', 'placeholder' => trans('deptinst.moderatorFirstNameInst'), 'id' => 'searchAdminFirstname']) !!}
							</div>
							<div class="col-sm-4">
								{!! Form::text('moderator_lastname', Input::get('moderator_lastname'), ['class' => 'form-control', 'placeholder' => trans('deptinst.moderatorLastNameInst'), 'id' => 'searchAdminLastname']) !!}
							</div>
							<div class="col-sm-4">
								{!! Form::text('moderator_email', Input::get('moderator_email'), ['class' => 'form-control', 'placeholder' => trans('deptinst.moderatorEmailInst'), 'id' => 'searchAdminEmail']) !!}
							</div>
						</div>
						
						<div class="small-gap"></div>
						
						<div>
							{!! Form::submit(trans('deptinst.search'), ['class' => 'btn btn-primary', 'id' => 'UserSubmitBtnNew']) !!}
						</div>
						
						
						{!! Form::close() !!}
						
					</div>							
				</div>
