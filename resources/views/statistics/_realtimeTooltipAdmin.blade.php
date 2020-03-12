<p style="color:#55BF3B;">{{trans('statistics.title')}}: {{$title}}<br/>
    {{trans('statistics.institution')}}: {{$institution}}<br/>
    {{trans('statistics.moderator')}}: {{$moderator}}<br/>
    {{trans('statistics.time')}}: {{$start}}-{{$end}} </p>
<p style="color:#8085e9;">{{trans('statistics.invited')}}: {{$total_users}}<br/>
    Desktop-Mobile: {{$total_desktop_mobile_participants}}<br/>
    {{trans('conferences.connectedUsers')}}: {{$total_connected_users}}</p>
<table>
    @foreach ($conf_users_collection->chunk(3) as $chunk)
        <tr>
            @foreach ($chunk as $conference_user)
                <td>
                <span style="color:{{$active_conference->participantConferenceStatus($conference_user->user_id) == 1 ? 'black' : 'red'}}; margin:10px;">
                {{App\User::findOrFail($conference_user->user_id)->email}}
                 </span>
                </td>
            @endforeach
        </tr>
    @endforeach
</table>