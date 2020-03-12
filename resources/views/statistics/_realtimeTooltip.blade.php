@if($active_conference->invisible == 0)
<p style="color:#55BF3B;">{{trans('statistics.title')}}: {{$title}}<br/>
{{trans('statistics.institution')}}: {{$institution}}<br/>
{{trans('statistics.moderator')}}: {{$moderator}}<br/>
{{trans('statistics.time')}}: {{$start}}-{{$end}}</p>
<p style="color:#8085e9;">{{trans('statistics.invited')}}: {{$total_users}}<br/>
Desktop-Mobile: {{$total_desktop_mobile_participants}}<br/>
{{trans('conferences.connectedUsers')}}: {{$total_connected_users}}
</p>
@endif