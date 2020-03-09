				<div class="col-md-12 col-sm-12 col-xs-12 collapse" id="collapseAdvancedDearch">
					<div class="small-gap"></div>
					<div class="well">
						<h4>Advanced Search <span class="glyphicon glyphicon-search" aria-hidden="true"></span></h4>
						<hr/>
					
						{!! Form::open(array('url' => Request::fullUrl(), 'method' => 'get', 'class' => 'form-horizontal', 'id' => 'CoordOrgForm', 'role' => 'form')) !!}
						
						<div class="row">
							<div class="col-sm-4">
								{!! Form::select('id', ['' => ''] + App\Institution::orderBy('title')->pluck('title', 'id')->toArray(), null, ['id' => 'searchInstitution', 'style' => 'width: 100%'])!!}
							</div>
							<div class="col-sm-4">
								{!! Form::text('url', null, ['class' => 'form-control', 'placeholder' => 'Website', 'id' => 'searchUrl']) !!}
							</div>
						</div>
						
						<div class="small-gap"></div>
						
						<div class="row">
							<div class="col-sm-4">
								{!! Form::text('contact_name', null, ['class' => 'form-control', 'placeholder' => trans('deptinst.moderatorNameDept'), 'id' => 'searchAdmin']) !!}
							</div>
							<div class="col-sm-4">
								{!! Form::text('contact_email', null, ['class' => 'form-control', 'placeholder' => trans('deptinst.moderatorEmailDept'), 'id' => 'searchEmail']) !!}
							</div>
							<div class="col-sm-4">
								{!! Form::text('contact_phone', null, ['class' => 'form-control', 'placeholder' => trans('deptinst.moderatorPhoneDept'), 'id' => 'searchPhone']) !!}
							</div>
						</div>
						
						<div class="small-gap"></div>
						
						<div>
							{!! Form::submit(trans('deptinst.search'), ['class' => 'btn btn-primary', 'id' => 'UserSubmitBtnNew']) !!}
						</div>
						
						
						{!! Form::close() !!}
						
					</div>							
				</div>
