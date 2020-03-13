<h4>{{trans('conferences.activeConferences')}}</h4>
<div class="col-md-12">
    <div id="activeConferencesTable">
        @foreach($active_conferences as $conference)
            <div id="ActiveConferenceRow-{{$conference->id}}"
                 style="padding:10px 5px 10px 5px; margin:10px; border:1px solid #ddd; overflow: auto;">
                <div class="col-md-12" style="margin-bottom:5px;">
                    <strong id="ActiveConferenceTitle-{{$conference->id}}">{{ $conference->title }}</strong>
                </div>
                <div class="col-md-12" style="margin-bottom:5px;">
                    {{trans('conferences.time')}}:
                    <span id="ActiveConferenceStartTime-{{$conference->id}}">{{$conference->getTime($conference->start)}}</span>
                    -
                    <span id="ActiveConferenceEndTime-{{$conference->id}}">{{$conference->getTime($conference->end)}}</span>,
                    {{trans('conferences.moderator')}}: {{ $conference->user->lastname }} {{ $conference->user->firstname }}
                </div>
                <div class="col-md-12">
                    @if($authenticated_user->hasAdminAccessToConference($conference))
                        <a href="/conferences/{{ $conference->id }}/manage">
                            <button id="ManageTele-{{ $conference->id }}" type="button"
                                    class="btn btn-primary btn-sm m-right btn-border"
                                    style="margin:5px;">{{trans('conferences.manage')}}</button>
                        </a>
                    @endif
                    @if($conference->isParticipant())
                        @php $disabled = ($authenticated_user->participantValues($conference->id)->enabled == 0 || $conference->room_enabled == 0) ? true : false; @endphp
                        @php  $device = $authenticated_user->participantValues($conference->id)->device; @endphp
                        <button id="GotoTele-{{ $conference->id }}"
                                data-device="{{$device}}"
                                @if($disabled || $conference->locked) disabled @endif
                                type="button"
                                style="margin:5px;"
                                class="GotoTele btn @if($disabled || $conference->locked)btn-danger @else btn-success @endif btn-sm m-right btn-border">
                            <span class="glyphicon glyphicon-log-in"></span>
{{--                            <span id="ActiveConferenceDevice-{{$conference->id}}">{{$device}}</span>--}}
                            <span id="status-{{$conference->id}}">
                                @if(!$disabled && !$conference->locked){{trans('conferences.connect')}}
                                @elseif($conference->locked) {{ trans('conferences.conference_room_locked') }}
                                @elseif($disabled) {{trans('application.inactive')}}
                                @endif
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    <div id="no_active_conferences_message" class="alert alert-danger"
         style="margin: 0 0 10px 0; @if(count($active_conferences) !== 0) display:none; @endif">
        {{trans('conferences.noActiveInvites')}}
    </div>
</div>

@foreach($active_conferences as $conference)
    <!-- MODAL H323 -->
    @if($conference->isParticipant())
    <div class="modal fade" id="H323Modal-{{ $conference->id }}" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false"
         aria-labelledby="H323ModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="UserLabel">{{trans('conferences.connectToConference')}}</h4>
                </div>
                <!-- .modal-header -->
                <div class="modal-body" style="overflow: auto;">
                    <div id="H323-step-one-{{ $conference->id }}" class="col-md-12">
                        <div class="col-sm-12" style="margin-bottom:20px;">
                            <span>{!! trans('conferences.h323_first_step_intro_text') !!} {!! trans('conferences.h323_first_step_text',['conference_id'=>$conference->id]) !!}</span>
                            <div id="H323-step-one-errors-{{ $conference->id }}" class="alert-danger" style="margin-top:5px; margin-bottom:5px;"></div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('FieldH323IP', trans('conferences.Η323ipAddress').':', ['class' => 'control-label col-sm-4']) !!}
                            <div class="col-sm-8">
                                {!! Form::text('H323IP',$authenticated_user->participantValues($conference->id)->identifier, ['class' => 'form-control', 'placeholder' => trans('conferences.enterIpUri'), 'id' => 'FieldH323IP-'.$conference->id]) !!}
                                <div class="help-block with-errors" style="margin:0;"></div>
                            </div>
                        </div>
                    </div>
                    <div id="H323-step-two-{{ $conference->id }}" class="col-md-12"  style="display:none;">
                        <div class="col-sm-12" style="margin-bottom:20px;">
                            <span>{!! trans('conferences.h323_firewall_notice',["minutes"=>5])!!}
                                <div class="timer" id="timer_wrapper_{{$conference->id}}" data-seconds-left="{{config('firewall.open_for')}}">
                                    <span>{{trans('conferences.timeRemain')}}: </span>
                                </div>
                            </span>
                            <span>{!! trans('conferences.h323_second_step_text',["ip_address"=>config('services.zoom.emea_ip_address'),"meeting_id"=>$conference->zoom_meeting_id]) !!}</span>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <span class="form-control"><strong>{{$conference->getDialString()['h323']}}</strong></span>
                                <div class="help-block with-errors" style="margin:0;"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <span class="form-control"><strong>{{$conference->getDialString()['sip']}}</strong></span>
                                <div class="help-block with-errors" style="margin:0;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- .modal-body -->
                <div class="modal-footer" style="margin-top:0;">
                        <button class="btn btn-success m-right btn-border"
                                id="saveIdentifier-{{ $conference->id }}">{!! trans('deptinst.next') !!}</button>
                    <button type="button" id="H323ModalButtonClose-{{ $conference->id }}"
                            class="btn btn-default m-right btn-border">{{trans('conferences.close')}}</button>
                </div>
            {!! Form::close() !!}
            <!-- .modal-footer -->
            </div>
            <!-- .modal-content -->
        </div>
        <!-- .modal-dialog -->
    </div>
    @endif
@endforeach
