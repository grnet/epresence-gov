<div class="modal fade" id="DeleteAccountModal" tabindex="-1" role="dialog" aria-labelledby="deleteAccountModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{trans('users.warning')}}</h4>
            </div> <!-- .modal-header -->
            <div class="modal-body">
                <form method="POST" action="/account/delete_anonymize" accept-charset="UTF-8" class="form-horizontal" role="form" enctype="multipart/form-data">
                    {{csrf_field()}}
                    @if($errors->has('delete_account_confirmation_email') || $errors->has('confirmation_email_not_matched'))
                        @foreach ($errors->all() as $message)
                            <p class="alert alert-danger" style="margin: 0px 15px 10px 15px">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                {{ $message }}
                            </p>
                        @endforeach
                    @endif
                    <p>{!! trans('site.deleteAccountLabel') !!}</p>
                    <div class="form-group">
                        <div class="col-sm-12">
                            {!! Form::text('delete_account_confirmation_email', null, ['class' => 'form-control','placeholder'=>trans('users.primaryEmail')]) !!}
                            <div class="help-block with-errors" style="margin:0px;"></div>
                        </div>
                    </div>
                    <div class="modal-footer" style="margin-top:0;">
                        <button  type="submit" class="btn btn-primary">OK</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{trans('site.cancel')}}</button>
                    </div> <!-- .modal-footer -->
                </form>
            </div> <!-- .modal-content -->
        </div> <!-- .modal-dialog -->
    </div> <!-- .modal -->
</div>