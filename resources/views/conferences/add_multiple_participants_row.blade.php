<div class="row" style="margin-top:10px; margin-bottom:10px;">
    <div class="col-md-12 col-sm-12 col-xs-12">
        @if(session()->has('multiple_participants_assigned') && session()->get('multiple_participants_assigned') > 0)
            <div class="alert alert-success">
                <span>{{session()->get('multiple_participants_assigned')}} {{trans('conferences.participants_assigned')}}</span>
            </div>
        @endif
        @if(session()->has('multiple_participants_error') && count(session()->get('multiple_participants_error')) > 0)
            <div class="alert alert-danger">
                <span>{{trans('conferences.multiple_participants_error')}}</span>
                <ul>
                    @foreach(session()->get('multiple_participants_error') as $email)
                        <li>{{$email}}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if(session()->has('multiple_participants_input_empty'))
            <div class="alert alert-danger">
                <span>{{trans('conferences.type_multiple_emails')}}</span>
            </div>
        @endif
        <form method="POST" class="form-horizontal" role="form"
              action="/conferences/assign_multiple_participants">
            {{csrf_field()}}
            <input type="hidden" name="conference_id" value="{{$conference->id}}">
            <label for="multiple_emails" class="form-control">{{trans('conferences.assign_multiple_participants')}}</label>
            <textarea id="multiple_emails" class="form-control" name="emails_input"
                      placeholder="{{trans('conferences.assign_multiple_participants_placeholder')}}"
                      required></textarea>
            <button class="btn btn-primary" style="margin-top:10px; margin-bottom:10px;">
                {{trans('conferences.adduser')}}
            </button>
        </form>
    </div>
</div>