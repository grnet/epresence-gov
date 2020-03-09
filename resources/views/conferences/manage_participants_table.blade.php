<table style="margin-top:10px; width:100%" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped table-bordered" id="participantsTable">
    <thead>
    <tr>
        <th class="cellDetails"></th>
        <th class="cellPName hidden-xs " id="sort_lastname">{{trans('conferences.fullName')}}</th>
        <th class="cellPEmail" >Email</th>
        <th class="cellPState hidden-xs " id="sort_state">{{trans('conferences.localUserTruncated')}}</th>
        <th class="cellPDevice">{{trans('conferences.device')}}</th>
        <th class="cellPStatus">{{trans('conferences.state')}}</th>
        <th class="cellPConnected">{{trans('conferences.connected')}}</th>
    </tr>
    </thead>
    <tbody>
    @php
    $participants = $conference->participants->sortBy('lastname');
    @endphp
    @foreach ($participants as $participant)
        <tr id="participantRow-{{$participant->id}}">
            <td class="cellDetails main_table" id="openParticipantDetails-{{ $participant->id }}"><span data-toggle="tooltip" data-placement="bottom" title="{{trans('conferences.details')}}" class="glyphicon glyphicon-zoom-in participant_details" aria-hidden="true"></span></td>
            <td class="cellPName hidden-xs">{{ $participant->lastname }} {{ $participant->firstname }}</td>
            <td class="cellPEmail">{{ $participant->email }}</td>
            <td class="cellPState hidden-xs"><span style="display:none">{{ $participant->state }}</span> {{ $participant->state_string($participant->state) }}</td>
            <td class="cellPDevice">
                    <span id="device-{{$participant->id}}">{{ $participant->participantValues($conference->id)->device }}</span>
            </td>
            <td class="center" id="participantStatus-{{ $participant->id }}"><button data-action="{{ $participant->participantValues($conference->id)->enabled ? 0 : 1 }}" id="ParticipantStatusButton-{{ $participant->id }}" type="button" class="btn btn-{{ $participant->status_button($participant->participantValues($conference->id)->enabled) }} btn-sm"><span class="icon_class glyphicon {{ $participant->status_icon($participant->participantValues($conference->id)->enabled) }}"></span><span class="message_container">{{ $participant->status_string($participant->participantValues($conference->id)->enabled) }}</span></button></td>
            <td class="center">
                <span class="label label-success" id="participantConnected-{{ $participant->id }}" @if($conference->participantConferenceStatus($participant->id) == 0) style="display:none;" @endif>{{trans('conferences.connected')}}</span>
                <span class="label label-danger" id="participantNotConnected-{{ $participant->id }}" @if($conference->participantConferenceStatus($participant->id) == 1) style="display:none;" @endif>{{trans('conferences.notConnected')}}</span>
            </td>
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
                                @php
                                $extra_emails_sso = $participant->extra_emails_sso()->toArray();
                                $extra_emails_custom = $participant->extra_emails_custom()->toArray();
                                @endphp
                                <div>
                                    @if((count($extra_emails_sso)+count($extra_emails_custom))>0)
                                        <span style="font-weight:bold;">{{trans('users.extraEmail')}}:</span>
                                    @endif
                                    @foreach($extra_emails_sso as $mail)
                                        <div style="color:green;">
                                            {{$mail['email']}} (sso {{trans('users.emailConfirmedShort')}})
                                        </div>
                                    @endforeach
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
                                    <strong>{{trans('conferences.department')}}:</strong> {{ $participant->departments->first()->title }} ({{ ($participant->customValues()['department']) }})
                                @else
                                    <strong>{{trans('conferences.institution')}}:</strong> {{ $participant->institutions->first()->title or trans('conferences.notDefinedYet') }}<br/>
                                    <strong>{{trans('conferences.department')}}:</strong> {{ $participant->departments->first()->title or trans('conferences.notDefinedYet') }}<br/>
                                @endif
                                @if($participant->pivot->joined_once == 1 && !empty($participant->pivot->intervals))
                                    <strong>{{trans('conferences.connection_intervals')}}</strong>
                                    @foreach(json_decode($participant->pivot->intervals) as $key=>$interval)
                                        {{trans('conferences.from')}}: {{Carbon\Carbon::parse($interval->join_time)->toTimeString()}} - {{trans('conferences.until')}}: {{Carbon\Carbon::parse($interval->leave_time)->toTimeString()}}@if($key!==(count(json_decode($participant->pivot->intervals))-1)),@endif
                                    @endforeach
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
