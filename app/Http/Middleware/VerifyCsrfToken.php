<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'conferences/assign_participant',
        'conferences/conferenceUserLogin',
        'conferences/conferenceUserDisconnect',
        'conferences/userConferenceDeviceAssign',
        'conferences/LogInAsGuestToMobile',
        'conferences/vidyoClientLockRoom',
        'conferences/vidyoMobileLockRoom',
        'conferences/openClient',
        'conferences/conferenceAddUserEmail',
        'users/delete_user',
        'users/disable_user',
        'set_cookie',
        'update_front_stats',
        'users/delete_user_image',
        'language/change_language',
        'new_sso_account/sendConfirmationEmailSSO',
        'zoom_hooks'
    ];
}
