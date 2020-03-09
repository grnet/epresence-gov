<h4>{{trans('conferences.futureConferences')}}</h4>
<div class="col-md-12">
    <div id="futureConferencesTable">
        @foreach($future_conferences as $conference)
            <div id="FutureConferenceRow-{{$conference->id}}"
                 style="padding:10px 5px 10px 5px; margin:10px; border:1px solid #ddd; overflow: auto;">

                <div class="col-md-12" style="margin-bottom:5px;">
                    <strong id="FutureConferenceTitle-{{$conference->id}}">{{ $conference->title }}</strong>
                </div>
                <div class="col-md-12" style="margin-bottom:5px;">
                    <span id="FutureConferenceStartDate-{{$conference->id}}" >{{ $conference->getDate($conference->start) }} και ώρα {{ $conference->getTime($conference->start) }}</span> -
                    <span id="FutureConferenceEndTime-{{$conference->id}}" >{{ $conference->getTime($conference->end) }}</span> με <span id="FutureConferenceDevice-{{$conference->id}}">{{$authenticated_user->participantValues($conference->id)->device}}</span>
                </div>
                <div class="col-md-12" style="margin-bottom:5px;">
                    Συντονιστής: {{ $conference->user->lastname }} {{$conference->user->firstname }}
                </div>

                @if($authenticated_user->hasAdminAccessToConference($conference))
                    <div class="col-md-12" style="margin-bottom:5px;">
                    <a
                            href="/conferences/{{ $conference->id }}/edit">
                        <button id="GotoTeleAdmin" type="button" class="btn btn-default"
                                data-toggle="tooltip" data-placement="right"
                                title="{{trans('conferences.edit')}}"><span
                                    class="glyphicon glyphicon-pencil"></span></button>
                    </a>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
<div class="alert alert-danger" id="no_future_conferences_message" style="margin: 0px 0px 10px 0px; @if(count($future_conferences) != 0) display:none; @endif">
   {{trans('conferences.noFutureInvites')}}
</div>
</div>
