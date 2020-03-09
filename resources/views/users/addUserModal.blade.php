
<!-- MODAL User start -->
<div class="modal fade" id="UserModal" tabindex="-1" role="dialog" aria-labelledby="UserModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="UserLabel">{{trans('users.addUser')}}</h4>
            </div> <!-- .modal-header -->
            <div class="modal-body">
                <div class="alert alert-danger" id="matched_error_container">
                    <strong>{{trans('users.email_matched_to_organisation')}} </strong>
                </div>
                <div class="alert alert-danger" id="not_matched_error_container">
                    <strong>{{trans('users.email_not_matched_to_organisation')}} </strong>
                </div>
                {!! Form::open(array('url' => 'users', 'method' => 'post', 'class' => 'form-horizontal', 'id' => 'CoordOrgForm', 'role' => 'form')) !!}

                <div class="form-group">
                    {!! Form::label('FieldUserEmail', 'Email:', ['class' => 'control-label col-sm-4 ']) !!}
                    <div class="col-sm-8">
                        {!! Form::text('email', null, ['class' => 'form-control', 'placeholder' => trans('users.emailRequired'), 'id' => 'FieldUserEmail']) !!}
                        <div class="help-block with-errors" style="margin:0"></div>
                    </div>
                </div>
                {!! Form::hidden('from', URL::full()) !!}
            </div> <!-- .modal-body -->

            <div class="modal-footer" style="margin-top:0;">

                {!! Form::submit(trans('users.save'), ['class' => 'btn btn-primary', 'id' => 'UserSubmitBtnNew', 'name' => 'add_new']) !!}
                <button type="button" id="UserModalButtonClose"
                        class="btn btn-default">{{trans('users.cancel')}}</button>
            </div> <!-- .modal-footer -->
        </div> <!-- .modal-content -->
        {!! Form::close() !!}
    </div> <!-- .modal-dialog -->
</div> <!-- .modal -->
<!-- modal User end -->