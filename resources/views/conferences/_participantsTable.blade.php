				<table style="margin-top:10px; width:100%" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped table-bordered" id="participantsTable">
					<thead>
						<tr>
							<th class="cellDetails"></th>
						@if(str_contains( Request::path(), 'edit'))
							<th><span data-toggle="tooltip" data-placement="top" title="Επιλογή όλων" aria-hidden="true"><input type="checkbox" id="checkAll"></span></th>
						@endif
                            <th class="cellPName hidden-xs sortingasc" id="sort_lastname">{{trans('conferences.fullName')}}</th>
							<th class="cellPEmail sorting" id="sort_email">Email</th>
							<th class="cellPState hidden-xs sorting" id="sort_state">{{trans('conferences.localUserTruncated')}}</th>
							<th class="cellPDevice">{{trans('conferences.device')}}</th>
						@if(str_contains( Request::path(), 'edit'))
                            <th class="center cellPSendEmail hidden-xs"><span class="glyphicon glyphicon-envelope" style="font-size:20px" data-toggle="tooltip" data-placement="top" title="{{trans('conferences.emailSentShort')}}"></span></th>
                            <th class="center cellPConfirm hidden-xs"><span class="glyphicon glyphicon-thumbs-up" style="font-size:20px" data-toggle="tooltip" data-placement="top" title="{{trans('conferences.confirmed')}}"></span></th>
                            <th class="cellPButton"></th>
						@endif
						@if(str_contains( Request::path(), 'manage'))
							<th class="cellPStatus">{{trans('conferences.state')}}</th>
							<th class="cellPConnected">{{trans('conferences.connected')}}</th>
						@endif
						@if(str_contains( Request::path(), 'details'))
							<th class="cellPStatus">{{trans('conferences.state')}}</th>
							<th class="cellPAddress">{{trans('conferences.address')}}</th>
							<th class="cellPDuration">{{trans('conferences.duration')}}</th>
						@endif
						</tr>
                    </thead>
                    <tbody>
					@php
					if(!empty($sort)){
						$sort_key = substr(head(array_keys($sort)), 5);
						$direction = head($sort);
						if($direction == 'asc'){
							$participants = $conference->participants->sortBy($sort_key);
						}elseif($direction == 'desc'){
							$participants = $conference->participants->sortByDesc($sort_key);
						}
					}else{
						$participants = $conference->participants->sortBy('lastname');
					}
					@endphp
						@foreach ($participants as $participant)
							<tr id="participantRow-{{$participant->id}}">
								<td class="cellDetails main_table" id="openParticipantDetails-{{ $participant->id }}"><span data-toggle="tooltip" data-placement="bottom" title="{{trans('conferences.details')}}" class="glyphicon glyphicon-zoom-in participant_details" aria-hidden="true"></span></td>
							@if(str_contains( Request::path(), 'edit'))
								<td class="cellPCheck">
								@if($participant->status != 0 )
								@if($participant->participantValues($conference->id)->invited == 1)
									{!! Form::checkbox('participants['.$participant->id.']', $participant->id, null, ['class' => 'check']) !!}
								@else
									{!! Form::checkbox('participants['.$participant->id.']', $participant->id, true, ['class' => 'check']) !!}
								@endif
								@else
									{!! Form::checkbox('participants['.$participant->id.']', $participant->id, false, ['disabled'=>true,'title'=>trans('users.user_inactive')]) !!}
								@endif
								</td>
							@endif
								<td class="cellPName hidden-xs">{{ $participant->lastname }} {{ $participant->firstname }}</td>
								<td class="cellPEmail">{{ $participant->email }}</td>
								<td class="cellPState hidden-xs"><span style="display:none">{{ $participant->state }}</span> {{ $participant->state_string($participant->state) }}</td>
								<td class="cellPDevice">
								@if(str_contains( Request::path(), 'edit'))
									<span style="display:none">participantDeviceIs{{ $participant->participantValues($conference->id)->device }}</span> {!! $conference->userConferenceDevice($participant) !!}
								@elseif(str_contains( Request::path(), 'manage') || str_contains( Request::path(), 'details'))
								   <span id="device-{{$participant->id}}">{{ $participant->participantValues($conference->id)->device }}</span>
								@endif
								</td>
							@if(str_contains( Request::path(), 'edit'))
								<td class="center cellPSendEmail hidden-xs"><span style="display:none">{{ $participant->participantValues($conference->id)->invited }}</span><span class="glyphicon {{ $participant->check_icon($participant->participantValues($conference->id)->invited) }}" aria-hidden="true"></span></td>
								<td class="center cellPConfirm hidden-xs"><span style="display:none">{{ $participant->participantValues($conference->id)->confirmed }}</span> <span class="glyphicon {{ $participant->check_icon($participant->participantValues($conference->id)->confirmed) }}" aria-hidden="true"></span></td>
								<td class="center cellPButton">
									<span data-toggle="tooltip" data-placement="bottom" title="{{trans('conferences.deleteParticipant')}}" aria-hidden="true"><button id="ParticipantBtnDelete-{{ $participant->id }}" type="button" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-remove"></span></button></span>
								</td>
							@endif
							@if(str_contains( Request::path(), 'manage'))
									<td class="center" id="participantStatus-{{ $participant->id }}"><button data-action="{{ $participant->participantValues($conference->id)->enabled ? 0 : 1 }}" id="ParticipantStatusButton-{{ $participant->id }}" type="button" class="btn btn-{{ $participant->status_button($participant->participantValues($conference->id)->enabled) }} btn-sm"><span class="icon_class glyphicon {{ $participant->status_icon($participant->participantValues($conference->id)->enabled) }}"></span><span class="message_container">{{ $participant->status_string($participant->participantValues($conference->id)->enabled) }}</span></button></td>
								@if($conference->participantConferenceStatus($participant->id) == '1')
									<td class="center" id="participantConnected-{{ $participant->id }}"><span style="display:none">{{ $conference->participantConferenceStatus($participant->id) }}</span> <span class="label label-success">{{trans('conferences.connected')}}</span></td>
								@else($conference->participantConferenceStatus($participant->id) == '0')
									<td class="center" id="participantConnected-{{ $participant->id }}"><span style="display:none">{{ $conference->participantConferenceStatus($participant->id) }}</span> <span class="label label-danger">{{trans('conferences.notConnected')}}</span></td>
								@endif
							@endif
							@if(str_contains( Request::path(), 'details'))
								@if(($participant->pivot->device == 'VidyoRoom') ||($participant->pivot->device == 'H323'))
									@if($participant->pivot->joined_once == 1)
										<td class="cellPStatus"><span class="label label-success">{{trans('conferences.hasConnected')}}</span></td>
										<td class="cellPAddress">{{ $participant->pivot->address }}</td>
										<td class="cellPDuration">{{ intval($participant->pivot->duration/60) }}m {{ $participant->pivot->duration%60 }}s</td>
									@else
										<td class="cellPStatus"><span class="label label-danger">{{trans('conferences.hasNotConnected')}}</span></td>
										<td class="cellPAddress">{{trans('conferences.hasNotConnected')}}</td>
										<td class="cellPDuration">0m</td>
									@endif
								@else
									@if($participant->pivot->joined_once == 1)
										<td class="cellPStatus"><span class="label label-success">{{trans('conferences.hasConnected')}}</span></td>
										<td class="cellPAddress">{{ $participant->pivot->address }}</td>
										<td class="cellPDuration">{{ intval($participant->pivot->duration/60) }}m {{ $participant->pivot->duration%60 }}s</td>
									@else
										<td class="cellPStatus"><span class="label label-danger">{{trans('conferences.hasNotConnected')}}</span></td>
										<td class="cellPAddress">{{trans('conferences.hasNotConnected')}}</td>
										<td class="cellPDuration">0m</td>
									@endif
								@endif							
							@endif
							</tr>
							<tr id="participantDetailsRow-{{$participant->id}}">
								<td colspan="12" class="hiddenRow">
									<div class="accordian-body collapse" id="participantDetails-{{ $participant->id }}"> 
										<table class="table">
											<tbody>
												<tr>
													<td>
														<strong>{{trans('conferences.fullName')}}:</strong> {{ $participant->lastname }} {{ $participant->firstname }}<br/>
														<strong>Email:</strong> {{ $participant->email }}<br/>
														<?php
														$extra_emails_sso = $participant->extra_emails_sso()->toArray();
														$extra_emails_custom = $participant->extra_emails_custom()->toArray();
														?>
														<div>
															@if((count($extra_emails_sso)+count($extra_emails_custom))>0)
																<span style="font-weight:bold;">{{trans('users.extraEmail')}}:</span>
															@endif
															@foreach($extra_emails_sso as $mail)
																<div style="color:green;">
																	{{$mail['email']}} (sso {{trans('users.emailConfirmedShort')}})
																</div>
															@endforeach
															@if(count($extra_emails_custom) > 0)
															<div style="padding-bottom:7px;">
																@foreach($extra_emails_custom as $mail)
																	@if($mail['confirmed'] == 0)
																		<div style="color:red;">
																			{{$mail['email']}} ({{trans('users.customExtraMail')}}  {{trans('users.emailNotConfirmedShort')}})
																		</div>
																	@else
																		<div style="color:green;">
																			{{$mail['email']}} ({{trans('users.customExtraMail')}}  {{trans('users.emailConfirmedShort')}})
																		</div>
																	@endif

																@endforeach
															</div>
															@endif
														</div>
														<strong>{{trans('users.confirmed')}}:</strong>
														@if($participant->confirmed == 0)
															{{trans('users.no')}}<br/>
														@else
															{{trans('users.yes')}}<br/>
														@endif
														<strong>{{trans('conferences.localUser')}}:</strong> {{ $participant->state_string($participant->state) }}<br/>
														<strong>{{trans('conferences.telephone')}}:</strong> {{ $participant->telephone }}<br/>
														<strong>{{trans('conferences.userType')}}:</strong> {{ trans($participant->roles->first()->label) }}<br/>
													@if($participant->institutions->count() > 0 && $participant->institutions->first()->slug == 'other')
														<strong>{{trans('conferences.institution')}}:</strong> {{ $participant->institutions->first()->title }} ({{ ($participant->customValues()['institution']) }})<br/>
														<strong>{{trans('conferences.department')}}:</strong> {{ $participant->departments->first()->title }} ({{ ($participant->customValues()['department']) }})<br/>
													@else
														<strong>{{trans('conferences.institution')}}:</strong> {{ $participant->institutions->first()->title or trans('conferences.notDefinedYet') }}<br/>
														<strong>{{trans('conferences.department')}}:</strong> {{ $participant->departments->first()->title or trans('conferences.notDefinedYet') }}<br/>
													@endif
														@if($participant->pivot->joined_once == 1 && !empty($participant->pivot->intervals))
															<strong>{{trans('conferences.connection_intervals')}}</strong>
															<span class="spanConnectionIntervals">
															@foreach(json_decode($participant->pivot->intervals) as $key=>$interval)
																{{trans('conferences.from')}}: {{Carbon\Carbon::parse($interval->join_time)->toTimeString()}} - {{trans('conferences.until')}}: {{Carbon\Carbon::parse($interval->leave_time)->toTimeString()}}@if($key!==(count(json_decode($participant->pivot->intervals))-1)),@endif
															@endforeach
															</span>
														@endif
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
