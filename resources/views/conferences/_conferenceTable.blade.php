				<table style="margin-top:10px; width:100%" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped table-bordered" id="conferenceTable">
					<thead>
						<tr>
							<th class="cellDetails"></th>
							<th class="cellID sorting" id="sort_id">{{trans('conferences.id')}}</th>
							<th class="cellDesc sorting" id="sort_title">{{trans('conferences.title')}}</th>
						@if(str_contains( Request::path(), 'all'))
							<th class="cellStartDate sortingdesc hidden-xs" id="sort_start">{{trans('conferences.startDate')}}</th>
						@else
							<th class="cellStartDate hidden-xs">{{trans('conferences.startDate')}}</th>
						@endif
						@if(str_contains( Request::path(), 'all'))
							<th class="cellStartTime hidden-xs">{{trans('conferences.startTime')}}</th>
						@else
							<th class="cellStartTime sortingdesc hidden-xs" id="sort_start">{{trans('conferences.startTime')}}</th>
						@endif
						@if(str_contains( Request::path(), 'all'))
							<th class="cellStartTime hidden-xs">{{trans('conferences.endTime')}}</th>
						@else
							<th class="cellStartTime sorting  hidden-xs" id="sort_end">{{trans('conferences.endTime')}}</th>
						@endif
							<th class="cellAdmin hidden-md hidden-sm hidden-xs">{{trans('conferences.moderator')}}</th>
							<th class="cellUHV  hidden-md hidden-sm hidden-xs">{{trans('conferences.participants')}}</th>
							<th class="cellInvisible hidden-md hidden-sm hidden-xs sorting" id="sort_invisible">{{trans('conferences.hidden')}}</th>
							<th class="cellButton"></th>
						</tr>
                    </thead>
                    <tbody>
                        @foreach ($conferences->getCollection()->all() as $conference)
							<tr>
								<td class="cellDetails main_table" id="openConferenceDetails-{{ $conference->id }}"><span data-toggle="tooltip" data-placement="bottom" title="{{trans('conferences.details')}}" class="glyphicon glyphicon-zoom-in conference_details" aria-hidden="true"></span></td>
								<td class="cellID">{{ $conference->id }}</td>							
								<td class="cellDesc">@if($conference->room_enabled == 1) <span class="label label-success">ΟΝ</span> @endif  {{ $conference->title }}</td>						
								<td class="cellStartDate hidden-xs">{{ $conference->getDate($conference->start) }}</td>							
								<td class="cellStartTime hidden-xs">{{ $conference->getTime($conference->start) }}</td>
								<td class="cellEndTime hidden-xs">{{ $conference->getTime($conference->end) }}</td>							
								<td class="cellAdmin hidden-md hidden-sm hidden-xs">{{ $conference->user->firstname }} {{ $conference->user->lastname }}</td>
								<td class="cellParticipants hidden-md hidden-sm hidden-xs">{{ $conference->participants->count() }}</td>
								<td class="cellInvisible hidden-md hidden-sm hidden-xs"><span class="glyphicon {{ $conference->invisible_icon($conference->invisible) }}" aria-hidden="true"><span style="display:none">{{ $conference->invisible }}</span></span>{{ $conference->invisible_string($conference->invisible) }}</td>
								<td class="cellButton center">
								@if($conference->isActiveOrFuture())
									@if(!$conference->test)
							 		<a href="/conferences/{{ $conference->id }}/edit"><button id="RowBtnEdit-{{ $conference->id }}" type="button" class="btn btn-default btn-sm m-right btn-border" data-toggle="tooltip" data-placement="bottom" title="{{trans('conferences.edit')}}"><span class="glyphicon glyphicon-pencil"></span></button></a>
									@else
								    <a href="/test-conferences/{{ $conference->id }}/edit"><button id="RowBtnEdit-{{ $conference->id }}" type="button" class="btn btn-default btn-sm m-right btn-border" data-toggle="tooltip" data-placement="bottom" title="{{trans('conferences.edit')}}"><span class="glyphicon glyphicon-pencil"></span></button></a>
								    @endif
								@else
							  		<a href="/conferences/{{ $conference->id }}/details"><button id="RowBtnEdit-{{ $conference->id }}" type="button" class="btn btn-default btn-sm m-right btn-border" data-toggle="tooltip" data-placement="bottom" title="{{trans('conferences.usageReport')}}"><span class="glyphicon glyphicon-tasks"></span></button></a>
								@endif
									@if(!$conference->test)
									<a href="/conferences/{{ $conference->id }}/copy"><button id="RowBtnCopy-{{ $conference->id }}" type="button" class="btn btn-default btn-sm m-right btn-border" data-toggle="tooltip" data-placement="bottom" title="{{trans('conferences.copy')}}"><i class="fa fa-files-o"></i></button></a>
									@else
									<button id="RowBtnCopy-{{ $conference->id }}" disabled type="button" class="btn btn-default btn-sm m-right btn-border" data-toggle="tooltip" data-placement="bottom" title="{{trans('conferences.copy')}}"><i class="fa fa-files-o"></i></button>
									@endif
								@if((Auth::user()->hasRole('SuperAdmin')) || !$conference->isPastConference())
									<button type="button" class="btn btn-danger btn-sm {{ $conference->rowButtonDeleteDisabled() }}" id="RowBtnDelete-{{ $conference->id }}" data-toggle="tooltip" data-placement="bottom" title="{{trans('conferences.delete')}}"><span class="glyphicon glyphicon-trash"></span></button>
								@endif
								</td>
							</tr>
							<tr>
								<td colspan="12" class="hiddenRow" style="width:100%">
									<div class="accordian-body collapse" id="conferenceDetails-{{ $conference->id }}"> 
										<table class="table">
											<tbody>
												<tr>
													<td>
														<strong>{{trans('conferences.id')}}: </strong>{{ $conference->id }}<br/>
														<strong>{{trans('conferences.title')}}: </strong><span class="spanConferenceTitle">{{ $conference->title }}</span><br/>
														<strong>{{trans('conferences.moderator')}}: </strong><span class="spanModeratorInfo">{{ $conference->user->firstname }} {{ $conference->user->lastname }}</span><br/>
													@if(Auth::user()->hasRole('SuperAdmin'))
														<span class="spanModeratorInfo">({{ $conference->user->email }}),{{trans('conferences.tel')}}.: {{ $conference->user->telephone}}</span><br/>
													@endif
													    <strong>{{trans('deptinst.institutionString')}} - {{trans('deptinst.departmentString')}}:</strong> {{ $conference->user->institutions->first()->title }}
													@if($conference->user->hasRole('DepartmentAdministrator'))
														&nbsp;- {{ $conference->user->departments->first()->title }}
													@endif
														<br/>
														<strong>{{trans('users.creationDate')}}: </strong> {{ $conference->created_at->format('d-m-Y H:i') }}<br/>
														<strong>{{trans('conferences.start')}}: </strong> {{ $conference->getDate($conference->start) }} {{ $conference->getTime($conference->start) }}<br/>
														<strong>{{trans('conferences.end')}}: </strong> {{ $conference->getDate($conference->end) }} {{ $conference->getTime($conference->end) }}<br/>
														<strong>{{trans('conferences.participants')}}: </strong> Desktop-Mobile: {{ $conference->participantsPerDevice('Desktop-Mobile') }} | H.323: {{ $conference->participantsPerDevice('H323') }}<br/>
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
				<span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> ({{ $conferences->firstItem() }} - {{ $conferences->lastItem() }}) {{trans('conferences.from')}} {{ $conferences->total() }}
				{!! $conferences->appends(Request::except('page'))->render() !!}