<table style="margin-top:10px; width:100%" cellpadding="0" cellspacing="0" border="0"
       class="table table-hover table-striped table-bordered" id="userTable">
    <thead>
    <tr>
        <th class="cellDetails"></th>
        <th class="cellName sortingasc" id="sort_lastname">{{trans('users.fullName')}}</th>
        <th class="cellRole">{{trans('users.role')}}</th>
        <th class="cellOrg hidden-xs">{{trans('users.institution')}}</th>
        <th class="cellDepart hidden-xs">{{trans('users.department')}}</th>
        <th class="cellStatus sorting" id="sort_status">{{trans('users.status')}}</th>
        <th class="cellCreationDate hidden-xs sorting" id="sort_createdAt">{{trans('users.creationDate')}}</th>
        <th class=""></th>
    </tr>
    </thead>
    <tbody>
    @foreach ($users->getCollection()->all() as $user)
        @if(str_contains( Request::path(), 'applications') && $user->institutions()->first()->institutionAdmins()->count() > 1)
            <tr class="warning">
        @else
            <tr>
                @endif
                <td class="cellDetails main_table" id="openUserDeatils-{{ $user->id }}"><span data-toggle="tooltip"
                                                                                              data-placement="bottom"
                                                                                              title="{{trans('users.details')}}"
                                                                                              class="glyphicon glyphicon-zoom-in user_details"
                                                                                              aria-hidden="true"></span>
                </td>
                <td class="cellName sorting main_table">{{ $user->lastname }} {{ $user->firstname }}</td>
                <td class="cellRole main_table">{{ trans($user->roles->first()->label) }}</td>
                <td class="cellOrg hidden-xs  main_table">{{ $user->institutions->first()->title  or trans('users.notDefinedYet')}}</td>
                <td class="cellDepart hidden-xs  main_table">{{ $user->departments->first()->title  or trans('users.notDefinedYet')}}</td>

                    <td id="cellStatus-{{ $user->id }}" class="cellStatus main_table"><span
                                class="glyphicon {{ $user->status_icon($user->status) }}" aria-hidden="true"><span
                                    style="display:none">{{ $user->status }}</span></span>{{ $user->status_string($user->status) }}
                    </td>
                <td class="cellCreationDate hidden-xs sorting main_table">{{ $user->getDate($user->created_at) }}</td>
                <td class="cellButton center main_table">
                    <a @if($user->confirmed) href="/users/{{ $user->id }}/edit" @else href="#" @endif>
                        <button id="RowBtnEdit-{{ $user->id }}" type="button" title="{!! trans('users.edit') !!}"
                                @if(!$user->confirmed) disabled
                                @endif class="btn btn-default btn-sm m-right btn-border"><span
                                    class="glyphicon glyphicon-pencil"></span></button>
                    </a>
                        <button id="RowBtnDelete-{{ $user->id }}" type="button"
                                class="btn {{ json_decode($user->statusUsersTableButton())->btn_bg }} btn-sm"
                                data-toggle="tooltip" data-placement="bottom"
                                title="{{ json_decode($user->statusUsersTableButton())->tooltipText }}"><span
                                    class="glyphicon {{ json_decode($user->statusUsersTableButton())->icon }}"
                                    id="SpanBtnDelete-{{ $user->id }}"></span></button>
                </td>
            </tr>
            <tr>
                <td colspan="9" class="hiddenRow">
                    <div class="accordian-body collapse" id="userDeatils-{{ $user->id }}">
                        <table class="table">
                            <tbody>
                            <tr>
                                <td>
                                    @if(!$user->confirmed)
                                          @if(empty($user->tax_id))
                                                <button class="btn btn-primary btn-sm m-right btn-border"
                                                        onclick="sendConfirmationEmail({{ $user->id }})"
                                                        style="float:right;">Αποστολή email ενεργοποίησης
                                                </button>
                                            @endif
                                        <br/>
                                    @endif
                                    <strong>ID:</strong> {{ $user->id}}<br/>
                                    <strong>{{trans('users.fullName')}}:</strong> {{ $user->lastname }} {{ $user->firstname }}<br/>
                                    <strong>Email:</strong> {{ $user->email }}<br/>
                                    <?php
                                    $extra_emails_sso = $user->extra_emails_sso()->toArray();
                                    $extra_emails_custom = $user->extra_emails_custom()->toArray();
                                    ?>
                                    <div>
                                        @if(count($extra_emails_sso)>0 || count($extra_emails_custom)>0)
                                            <span style="font-weight:bold;">{{trans('users.extraEmail')}}:</span>
                                        @endif
                                        @foreach($extra_emails_sso as $mail)
                                            <div style="color:green;">
                                                {{$mail['email']}} (sso {{trans('users.emailConfirmedShort')}})
                                            </div>
                                        @endforeach
                                        @if(count($extra_emails_custom)>0)
                                            <div style="padding-bottom:7px;">
                                                @foreach($extra_emails_custom as $mail)
                                                    @if($mail['confirmed'] == 0)
                                                        <div style="color:red;">
                                                            {{$mail['email']}} ({{trans('users.customExtraMail')}} {{trans('users.emailNotConfirmedShort')}})
                                                        </div>
                                                    @else
                                                        <div style="color:green;">{{$mail['email']}} ({{trans('users.customExtraMail')}} {{trans('users.emailConfirmedShort')}})
                                                        </div>
                                                    @endif

                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <strong>{{trans('users.confirmed')}}:</strong>
                                    @if($user->confirmed == 0)
                                        {{trans('users.no')}}<br/>
                                    @else
                                        {{trans('users.yes')}}<br/>
                                    @endif
                                    <strong>{{trans('users.telephone')}}:</strong> {{ $user->telephone }}<br/>
                                    <strong>{{trans('users.role')}}:</strong> {{ trans($user->roles->first()->label) }}
                                    <br/>
                                        <strong>{{trans('users.institution')}}:</strong> {{ $user->institutions->first()->title or trans('users.notDefinedYet') }}
                                        <br/>
                                        <strong>{{trans('users.department')}}:</strong> {{ $user->departments->first()->title or trans('users.notDefinedYet') }}
                                        <br/>
                                        <strong>{{trans('site.termsAcceptanceAdmin')}}:</strong>@if(!empty($user->accepted_terms)) {{ Carbon\Carbon::parse($user->accepted_terms)->toDateTimeString()}} @else {!! trans('users.no') !!} @endif
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

<span class="glyphicon glyphicon-eye-open"
      aria-hidden="true"></span> ({{ $users->firstItem() }} - {{ $users->lastItem() }}) {{trans('users.from')}} {{ $users->total() }}

{!! $users->appends(Request::except('page'))->render() !!}
