				<table style="margin-top:10px; width:100%" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped table-bordered" id="institutionTable">
					<thead>
						<tr>
							<th class="cellDetails"></th>
							<th class="cellName sortingasc" id="sort_title">{{trans('deptinst.institution')}}</th>
							<th class="cellButton"></th>
						</tr>
                    </thead>
                    <tbody>
                    @foreach ($institutions->getCollection()->all() as $institution)
						<tr>
							<td class="cellDetails main_table" id="openInstitutionDetails-{{ $institution->id }}"><span data-toggle="tooltip" data-placement="bottom" title="{{trans('deptinst.details')}}" class="glyphicon glyphicon-zoom-in institution_details" aria-hidden="true"></span></td>
							<td class="cellName main_table">{{ $institution->title }}</td>
							<td class="cellButton center main_table">
								<a href="/institutions/{{ $institution->id }}/edit"><button id="RowBtnEdit-{{ $institution->id }}" type="button" class="btn btn-default btn-sm m-right btn-border" data-toggle="tooltip" data-placement="bottom" title="{{trans('deptinst.editInstitution')}}"><span class="glyphicon glyphicon-pencil"></span></button></a>
								<button id="RowBtnDelete-{{ $institution->id }}" type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="bottom" title="{{trans('deptinst.deleteInstitution')}}"><span class="glyphicon glyphicon-trash"></span></button>
								<a href="/institutions/{{ $institution->id }}/departments"><button id="RowBtnDep-{{ $institution->id }}" type="button" class="btn btn-default btn-sm m-right btn-border" data-toggle="tooltip" data-placement="bottom" title="{{trans('deptinst.institutionDepts')}}"><span class="glyphicon glyphicon-list"></span></button></a>
							</td>
						</tr>
						<tr>
							<td colspan="9" class="hiddenRow">
								<div class="accordian-body collapse" id="institutionDetails-{{ $institution->id }}"> 
									<table class="table">
										<tbody>
											<tr>
												<td>
													<strong>{{trans('deptinst.institution')}}:</strong> {{ $institution->title }}<br/>
													<strong>{{trans('deptinst.contactName')}}:</strong> {{ isset($institution->contact_name) ? $institution->contact_name : '-' }}<br/>
													<strong>{{trans('deptinst.contactDetails')}}:</strong> Email: {{ isset($institution->contact_email) ? $institution->contact_email : '-' }}, Τηλ.: {{ isset($institution->contact_phone) ? $institution->contact_phone : '-' }}<br/>
													<strong>{{trans('deptinst.instModerators')}}:</strong><br/>
													@foreach($institution->institutionAdmins() as $institutionAdmin)
														<a href="/users/{{ $institutionAdmin->id }}/edit">{{ $institutionAdmin->firstname }} {{ $institutionAdmin->lastname }}</a><br/>
													@endforeach
												</td>
											</tr>
										</tbody>
									</table>
								</div> 
							</td>
						</tr>
					@endforeach           
					</tbody>
				</table>
				
				<span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> ({{ $institutions->firstItem() }} - {{ $institutions->lastItem() }}) από {{ $institutions->total() }}
				
				{!! $institutions->appends(Request::except('page'))->render() !!}
