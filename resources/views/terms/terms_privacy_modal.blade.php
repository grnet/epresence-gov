<div class="modal fade" id="acceptTermsModal" tabindex="-1" role="dialog" aria-labelledby="deleteAccountModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{trans('site.acceptTerms_Policy')}}</h4>
            </div> <!-- .modal-header -->
            <div class="modal-body">
                <h3>{{trans('site.termsSite')}}</h3>
                <div class="scroll_box">
                {!!trans('terms.termsSiteFullText')!!}
                </div>
                <div class="checkbox_container col-md-12">
                    <label>{{trans('site.termsAcceptance')}} <input id="terms_accept_modal" class="accepted_terms_check" type="checkbox"></label>
                </div>
                <h3>{{trans('site.privacy_policy')}}</h3>
                <div class="scroll_box">
                    {!!trans('terms.privacy_notice')!!}
                </div>
                <div class="checkbox_container col-md-12">
                <label>{{trans('site.privacyPolicyAcceptance')}} <input class="accepted_terms_check" id="privacy_accept_modal" type="checkbox"></label>
                </div>
                <div class="col-md-12">
                    <a href="/auth/logout" class="btn btn-danger">{{trans('site.cancel')}}</a>
                    <button type="button" class="btn btn-primary disabled_button" id="accept_modal_terms_button" data-placement="top" data-toggle="tooltip" data-original-title="{{trans('site.acceptTerms_Policy')}}">OK</button>
                </div>
            </div> <!-- .modal-content -->
        </div> <!-- .modal-dialog -->
    </div> <!-- .modal -->
</div>
<style>
    .scroll_box{
        max-height:250px;
        overflow-y: auto;
        overflow-x: hidden;
        border:1px solid #e5e5e5;
        padding:10px;
    }
    .scroll_box p {
        font-size:13px;
    }
    .checkbox_container{
        margin-top:10px;
        margin-bottom:10px;
    }
    .modal-body{
        overflow: auto;
    }

    .modal-body input {
        margin-top:10px;
    }
    .disabled_button{
        background-color: gainsboro !important;
        border-color: gainsboro !important;
        cursor: not-allowed;
    }
</style>
<script>
    $(document).ready(function () {
        var accept_modal_terms_button = $("#accept_modal_terms_button");
        $("#acceptTermsModal").modal({backdrop: 'static', keyboard: false});


        $(".accepted_terms_check").on("change", function () {

            var checked_count = $(".accepted_terms_check:checked").length;
            if (checked_count === 2) {
                accept_modal_terms_button.removeClass("disabled_button");
                accept_modal_terms_button.attr('data-original-title', null);
                accept_modal_terms_button.attr('title', null);
            } else {
                accept_modal_terms_button.addClass("disabled_button");
                accept_modal_terms_button.attr('data-original-title', '{!!trans('site.acceptTerms_Policy')!!}');
            }
        });
        accept_modal_terms_button.on("click", function () {
            if (!$(this).hasClass("disabled_button")) {
                $.post("/accept_terms_ajax", {
                    _token: "{{csrf_token()}}",
                })
                    .done(function (response) {
                        if(response.status==='success')
                        location.reload();

                    });
            }else{
                console.log('Please accept terms first');
            }
        });
    });
</script>