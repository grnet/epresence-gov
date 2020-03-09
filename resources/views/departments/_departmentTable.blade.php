				<table style="margin-top:10px; width:100%" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped table-bordered" id="departmentTable">
					<thead>
						<tr>
							<th class="cellDetails"></th>
							<th class="cellName sortingasc" id="sort_title">Τμήμα</th>
							<th class=""></th>
						</tr>
                    </thead>
                    <tbody>
                    @foreach ($departments->getCollection()->all() as $department)
						<tr>
							<td class="cellDetails main_table" id="openDepartmentDetails-{{ $department->id }}"><span data-toggle="tooltip" data-placement="bottom" title="{{trans('deptinst.details')}}" class="glyphicon glyphicon-zoom-in department_details" aria-hidden="true"></span></td>
							<td class="cellName main_table">{{ $department->title }}</td>
							<td class="cellButton center main_table">
								<a href="/departments/{{ $department->id }}/edit"><button id="RowBtnEdit-{{ $department->id }}" type="button" class="btn btn-default btn-sm m-right btn-border" data-toggle="tooltip" data-placement="bottom" title="{{trans('deptinst.editDepartment')}}"><span class="glyphicon glyphicon-pencil"></span></button></a>
								<button id="RowBtnDelete-{{ $department->id }}" type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="bottom" title="{{trans('deptinst.deleteDepartment')}}"><span class="glyphicon glyphicon-trash"></span></button>
							</td>
						</tr>
						<tr>
							<td colspan="9" class="hiddenRow">
								<div class="accordian-body collapse" id="departmentDetails-{{ $department->id }}"> 
									<table class="table">
										<tbody>
											<tr>
												<td>
													<strong>{{trans('deptinst.department')}}:</strong> {{ $department->title }}<br/>
													<strong>{{trans('deptinst.deptModerators')}}:</strong><br/>
													@foreach($department->departmentAdmins() as $departmentAdmin)
														<a href="/users/{{ $departmentAdmin->id }}/edit">{{ $departmentAdmin->firstname }} {{ $departmentAdmin->lastname }}</a><br/>
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
				
				<span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> ({{ $departments->firstItem() }} - {{ $departments->lastItem() }}) από {{ $departments->total() }}
				
				{!! $departments->appends(Request::except('page'))->render() !!}
