<h4>{{trans('statistics.systemStats')}}</h4>
<ul>
    <li>{{trans('statistics.capacityFor')}} Desktop-Mobile: <span class="period_reports">270</span></li>
    <li>{{trans('statistics.capacityFor')}} {{trans('statistics.terminals')}} Η.323: <span class="period_reports">25 HD / 75 SD</span></li>
    <li>{{trans('statistics.maxReservationUsers')}} Desktop-Mobile {{trans('statistics.forSingleConf')}}: <span class="period_reports">{{ App\Settings::option('conference_maxDesktop') }}</span></li>
    <li>{{trans('statistics.maxReservationUsers')}} {{trans('statistics.terminals')}} Η.323: <span class="period_reports">{{ App\Settings::option('conference_maxH323') }}</span></li>
</ul>
<h4>{{trans('statistics.usageStats')}}</h4>
<ul>
    <li>{{trans('statistics.totalConferences')}}: <span class="period_reports">{{ json_decode($initial_period_stats)->all_conferences }}</span>
        <ul>
            <li>{{trans('statistics.drConfs')}}: <span class="period_reports">{{ json_decode($initial_period_stats)->postgraduate_conferences }}</span></li>
            <li>{{trans('statistics.electConfs')}}: <span class="period_reports">{{ json_decode($initial_period_stats)->elector_conferences }}</span></li>
            <li>{{trans('statistics.testConfsCap')}}: <span class="period_reports">{{ json_decode($initial_period_stats)->test_conferences }}</span></li>
            <li>{{trans('statistics.otherConfs')}}: <span class="period_reports">{{ json_decode($initial_period_stats)->other_conferences }}</span></li>
        </ul>
    </li>
    <li>{{trans('statistics.oneOrMoreModerators')}}: <span class="period_reports">{{ json_decode($initial_period_stats)->authInstitutions }}</span>
        <ul>
            <li>{{trans('statistics.tenConfs')}}: <span class="period_reports">{{ json_decode($initial_period_stats)->tenConferences }}</span></li>
            <li>{{trans('statistics.fiveConfs')}}: <span class="period_reports">{{ json_decode($initial_period_stats)->fiveConferences }}</span></li>
            <li>{{trans('statistics.oneConf')}}: <span class="period_reports">{{ json_decode($initial_period_stats)->oneConferences }}</span></li>
        </ul>
    </li>
    <li>{{trans('statistics.topTenNoConf')}}
        <ul>
            @if(!empty(json_decode($initial_period_stats)->sortedInstitutionsByNum))
                @foreach(json_decode($initial_period_stats)->sortedInstitutionsByNum as $institution)
                    <li>{{ $institution->title }}</li>
                @endforeach
            @endif
        </ul>
    </li>
    <li>{{trans('statistics.topTenDuration')}}
        <ul>
            @if(!empty(json_decode($initial_period_stats)->sortedInstitutionsByDuration))
                @foreach(json_decode($initial_period_stats)->sortedInstitutionsByDuration as $institution)
                    <li>{{ $institution->title }}</li>
                @endforeach
            @endif
        </ul>
    </li>
    <li>{{trans('statistics.uniqueUsers')}}: <span class="period_reports">{{ json_decode($initial_period_stats)->textΑllUsersNow }}</span></li>
    <li>{{trans('statistics.uniqueDesktop')}}: <span class="period_reports">{{ json_decode($initial_period_stats)->textDesktopUsers }}</span></li>
    <li>{{trans('statistics.uniqueH323')}}: <span class="period_reports">{{ json_decode($initial_period_stats)->textH323Users }}</span></li>
    <li>{{trans('statistics.averageParticipants')}}: <span class="period_reports">{{ json_decode($initial_period_stats)->average_participants_on_non_test_conference }}</span>
        <ul>
            <li>{{trans('statistics.averageDesktop')}}: <span class="period_reports">{{ json_decode($initial_period_stats)->average_desktop_mobile_on_non_test_conference }}</span></li>
            <li>{{trans('statistics.averageH323')}}: <span class="period_reports">{{ json_decode($initial_period_stats)->average_h323_on_non_test_conference }}</span></li>
        </ul>
    </li>
    <li>{{trans('statistics.earliestConfTime')}}: <span class="period_reports">{{ json_decode($initial_period_stats)->min_hour_start }}</span></li>
    <li>{{trans('statistics.latestConfTime')}}: <span class="period_reports">{{ json_decode($initial_period_stats)->max_hour_end }}</span></li>
    <li>{{trans('statistics.averageDuration')}}: <span class="period_reports">{{ json_decode($initial_period_stats)->average_duration }}</span></li>
</ul>
{{--<li>{{trans('statistics.workingΗours')}}:--}}
{{--<ul>--}}
{{--<li>{{trans('statistics.at')}} <span class="period_reports">{{ json_decode($initial_period_stats)->capacity->DMmaxDMAllowedCount }}</span>% {{trans('statistics.desktopUtilization')}} 100%</li>--}}
{{--<li>{{trans('statistics.at')}} <span class="period_reports">{{ json_decode($initial_period_stats)->capacity->DMmaxDMAllowed70Count }}</span>% {{trans('statistics.desktopUtilization')}} 70%</li>--}}
{{--<li>{{trans('statistics.at')}} <span class="period_reports">{{ json_decode($initial_period_stats)->capacity->DMmaxDMAllowed50Count }}</span>% {{trans('statistics.desktopUtilization')}} 50%</li>--}}
{{--<li>{{trans('statistics.at')}} <span class="period_reports">{{ json_decode($initial_period_stats)->capacity->HmaxDMAllowedCount }}</span>% {{trans('statistics.h323Utilization')}} 100%</li>--}}
{{--<li>{{trans('statistics.at')}} <span class="period_reports">{{ json_decode($initial_period_stats)->capacity->HmaxDMAllowed70Count }}</span>% {{trans('statistics.h323Utilization')}} 70%</li>--}}
{{--<li>{{trans('statistics.at')}} <span class="period_reports">{{ json_decode($initial_period_stats)->capacity->HmaxDMAllowed50Count }}</span>% {{trans('statistics.h323Utilization')}} 50%</li>--}}
{{--</ul>--}}
{{--</li>--}}