{{--At least one confirmed email available--}}
@if($user->confirmation_state !== "pending_email")
  @include("account_activation.sso_activate")
@else
    @include("account_activation.sso_pending_mail")
@endif