<table style="margin-top:10px; width:100%" cellpadding="0" cellspacing="0" border="0"
       class="table table-hover table-striped table-bordered" id="userTable">
    <thead>
    <tr>
        <th class="cellDetails"></th>
        <th class="cellId">User Id</th>
        <th class="cellName sortingasc" id="sort_lastname">{{trans('users.fullName')}}</th>
        <th class="cellRole">{{trans('users.requestedRole')}}</th>
        <th class="cellOrg hidden-xs">{{trans('users.institution')}}</th>
        <th class="cellDepart hidden-xs">{{trans('users.department')}}</th>
        <th class="cellStatus sorting" id="sort_status">{{trans('users.status')}}</th>
        <th class="cellCreationDate hidden-xs sorting" id="sort_createdAt">{{trans('users.creationDate')}}</th>
        <th class=""></th>
    </tr>
    </thead>
    <tbody>
    @foreach ($applications as $application)
        @if(isset($application->user))

            <tr @if($application->app_state == "new")class="warning" @endif>
                <td class="cellDetails main_table" id="openUserDetails-{{ $application->id }}"><span
                            data-toggle="tooltip" data-placement="bottom" title="{{trans('users.details')}}"
                            class="glyphicon glyphicon-zoom-in user_details" aria-hidden="true"></span></td>
                <td>{{$application->user->id}}</td>
                <td class="cellName sorting main_table">{{ $application->user->lastname }} {{ $application->user->firstname }}</td>
                <td class="cellRole main_table">{{ trans($application->role->label) }}</td>
                <td class="cellOrg hidden-xs  main_table">{{ $application->user->institutions()->first()->title  or trans('users.notDefinedYet')}}</td>
                <td class="cellDepart hidden-xs  main_table">{{ $application->user->departments()->first()->title  or trans('users.notDefinedYet')}}</td>
                <td class="cellState hidden-xs main_table">{{ $application->getStatusString() }}</td>
                <td class="cellCreationDate hidden-xs sorting main_table">{{ $application->created_at }}</td>
                <td class="cellButton center main_table">
                    @if($application->app_state == "new")
                        <button id="RowBtnAccept-{{ $application->id }}" type="button" class="btn btn-success btn-sm"
                                data-toggle="tooltip" data-placement="bottom"
                                title="{!! trans("application.acceptApplication") !!}"><span
                                    class="glyphicon glyphicon glyphicon-ok"></span></button>
                        <button id="RowBtnDecline-{{ $application->id }}" type="button" class="btn btn-danger btn-sm"
                                data-toggle="tooltip" data-placement="bottom"
                                title="{!! trans("application.declineApplication") !!}"><span
                                    class="glyphicon glyphicon-ban-circle"></span></button>
                    @endif
                </td>
            </tr>
            <tr>
                <td colspan="10" class="hiddenRow">
                    <div class="accordian-body collapse" id="userDetails-{{ $application->id }}">
                        <table class="table">
                            <tbody>
                            <tr>
                                <td>
                                    <span class="table-span"><strong>Σχόλια αίτησης:</strong> {{ $application->comment }}</span><br/>
                                    <span class="table-span"><strong>{{trans('users.fullName')}} :</strong> {{ $application->user->lastname }} {{ $application->user->firstname }} </span><br/>
                                    <span class="table-span"><strong>Email:</strong> {{ $application->user->email }}<br/></span>
                                    <div>
                                        @if((count($application->user->extra_emails_sso()->toArray())+count($application->user->extra_emails_custom()->toArray()))>0)
                                            <span style="font-weight:bold;">{{trans('users.extraEmail')}}:</span>
                                            @foreach($application->user->extra_emails_sso()->toArray() as $mail)
                                                <div style="color:green;">
                                                    {{$mail['email']}} (sso {{trans('users.emailConfirmedShort')}})
                                                </div>
                                            @endforeach
                                            <div style="padding-bottom:7px;">
                                                @foreach($application->user->extra_emails_custom()->toArray() as $mail)
                                                    @if($mail['confirmed'] == 0)
                                                        <div style="color:red;">
                                                            {{$mail['email']}}
                                                            ({{trans('users.customExtraMail')}}  {{trans('users.emailNotConfirmedShort')}}
                                                            )
                                                        </div>
                                                    @else
                                                        <div style="color:green;">
                                                            {{$mail['email']}}
                                                            ({{trans('users.customExtraMail')}}  {{trans('users.emailConfirmedShort')}}
                                                            )
                                                        </div>
                                                    @endif
                                                @endforeach
                                                @endif
                                            </div>
                                    </div>
                                    <span class="table-span"><strong>{{trans('users.confirmed')}}:</strong></span>
                                    {{trans('users.yes')}}<br/>
                                    <span class="table-span"><strong>{{trans('users.telephone')}}:</strong> {{ $application->user->telephone }}</span>
                                    <br/>
                                    {{--//FIX CUSTOM VALUES--}}
                                    <strong>{{trans('users.requestedRole')}}:</strong> {{ trans($application->role->label) }}<br/>
                                    <strong>{{trans('users.requestedInstitution')}}:</strong> {{ trans($application->institution->title) }}<br/>
                                    <strong>{{trans('users.requestedDepartment')}}:</strong> {{ trans($application->department->title) }}<br/>
                                    <span class="table-span"><strong>{{trans('users.institution')}} :</strong> {{ $application->user->institutions->first()->title or trans('users.notDefinedYet') }}</span><br/>
                                    <span class="table-span"><strong>{{trans('users.department')}}:</strong> {{ $application->user->departments->first()->title or trans('users.notDefinedYet') }}</span>
                                    <br/><span class="table-span"><strong>{{trans('users.otherInstitutionModerators')}}:</strong><br/>
                                    @foreach($application->user->institutions()->first()->institutionAdmins() as $institutionAdmin)
                                            <a href="/users/{{ $institutionAdmin->id }}/edit"
                                               target="_blank">{{ $institutionAdmin->firstname }} {{ $institutionAdmin->lastname }}</a>
                                            <br/></span>
                                    @endforeach
                                    @if($application->role->name == "DepartmentAdministrator")
                                        <br/><span class="table-span"><strong>{{trans('users.otherDepartmentModerators')}}:</strong></span><br/>
                                        @foreach($application->user->departments()->first()->departmentAdmins() as $departmentAdmin)
                                            <a href="/users/{{ $departmentAdmin->id }}/edit"
                                               target="_blank">{{ $departmentAdmin->firstname }} {{ $departmentAdmin->lastname }}</a>
                                            <br/>
                                        @endforeach
                                    @endif
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        @endif
    @endforeach
    </tbody>
</table>
<span class="glyphicon glyphicon-eye-open"
      aria-hidden="true"></span> ({{ $applications->firstItem() }} - {{ $applications->lastItem() }}) {{trans('users.from')}} {{ $applications->total() }}
{!! $applications->appends(Request::except('page'))->render() !!}