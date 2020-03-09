function update_warning_message(email_matched, local_state_checkbox, sso_state_checkbox, matched_error_container, not_matched_error_container) {

    if (local_state_checkbox.is(':checked') && email_matched) {
        not_matched_error_container.slideUp();
        matched_error_container.slideDown();

    } else if (sso_state_checkbox.is(':checked') && !email_matched) {
        matched_error_container.slideUp();
        not_matched_error_container.slideDown();
    } else {
        matched_error_container.slideUp();
        not_matched_error_container.slideUp();
    }
}

function check_mail_properties(email_to_check, matched_error_container, not_matched_error_container, local_state_checkbox, sso_state_checkbox, _token) {

    if (email_to_check.length > 4 && (local_state_checkbox.is(':checked') || sso_state_checkbox.is(':checked') )) {
        $.post("/users/check_mail_properties", {_token: _token, mail: email_to_check})
            .done(function (data) {
                update_warning_message(data.matched, local_state_checkbox, sso_state_checkbox, matched_error_container, not_matched_error_container);
            });
    }
}
