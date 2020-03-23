<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {

    Storage::append("logs/webhooks.log","TEST");

    $client = new \Asikamiotis\JiraClient\JiraClient();
    $client->test_api();


    return view('epresence');
});




//Access static page
//Route::get('/access', function () {
//    return view('access', ['lastname' => null, 'name' => null, 'emails' => null, 'state' => 'local', 'persistent_id' => null]);
//})->name('access');


Route::get('update_front_stats','ConferencesController@update_front_stats');

//Support static page

//Contact

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/cookies','SupportPageController@show_cookies_page')->name('cookies');
Route::get('/support/{type?}','SupportPageController@index')->name('support');
Route::post('/support/downloads','SupportPageController@store_download');
Route::post('/support/downloads/delete','SupportPageController@delete_download');
Route::post('/support/downloads/get_download_details_ajax','SupportPageController@get_download_details_ajax');
Route::patch('/support/downloads/update','SupportPageController@update_download');
Route::post('contact', 'EmailsController@contact_email');

//Demo room static page

Route::get('demo-room', 'DemoRoomController@index');
Route::get('join_demo_room','DemoRoomController@join_demo_room');
Route::get('demo-room/manage','DemoRoomController@manage');
Route::post('/demo_room/disconnectAll','DemoRoomController@disconnectAll');

//My account page

Route::get('account','AccountController@showAccount');



//Account Methods

//Extra emails managed by user
Route::get('account/emails','AccountController@showManageEmails');
Route::post('account/emails/add_new','ExtraEmailsController@addExtraMail');
Route::post('account/emails/deleteExtraEmail', 'ExtraEmailsController@deleteExtraMail');
Route::post('account/emails/resend_extra_email_confirmation', 'ExtraEmailsController@resend_extra_email_confirmation');
Route::post('account/emails/makePrimary', 'ExtraEmailsController@makePrimary');
Route::patch('account/update_local','AccountController@UpdateLocalAccount');
Route::patch('account/update_sso','AccountController@UpdateSsoAccount');

// Account activation

Route::get('confirm_sso_email/{token}','AccountController@confirm_sso_email')->name('confirm-sso-email');
Route::get('account-activation','AccountController@accountActivation')->name('account-activation');
Route::post('account-activation', 'AccountController@ssoAccountActivation')->name('account-activation');
Route::post('account/delete_anonymize','AccountController@delete_anonymize');
Route::get('request_role_change', 'AccountController@redirect_to_request_role_change');

//Applications

Route::post('users/request_role_change', 'ApplicationController@requestRoleChange');
Route::post('applications/decline_application','ApplicationController@decline_application');
Route::post('applications/accept_application','ApplicationController@accept_application');
Route::get('administrators/applications', 'ApplicationController@index');
Route::get('/email_activation/{token}', 'ExtraEmailsController@ConfirmExtraEmail');

// Change Language
//Route::post('language/change_language', 'LanguageController@change_language');

//Statistics

Route::get('index',function(){
    return view('epresence');
});

Route::get('statistics', 'StatisticsController@index');
Route::get('statistics/report', 'StatisticsController@report');
Route::get('statistics/personalized', 'StatisticsController@personalised_statistics');
Route::get('statistics/report_select_period', 'StatisticsController@report_select_period');
Route::get('statistics/periods', 'StatisticsController@periods');
Route::get('statistics/demo-room', 'StatisticsController@demo_room');
Route::get('statistics/utilization', 'StatisticsController@utilization_statistics');
Route::post('statistics/periods', 'StatisticsController@select_period');
Route::get('statistics/realtime/conferences', 'StatisticsController@realtime_count_conferences_refresh');
Route::get('statistics/realtime/users_no_desktop', 'StatisticsController@realtime_count_desktop_refresh');
Route::get('statistics/realtime/users_no_h323', 'StatisticsController@realtime_count_h323_refresh');
Route::get('statistics/realtime/users_per_room', 'StatisticsController@realtime_users_per_room_refresh');
Route::get('statistics/realtime/users_daily', 'StatisticsController@users_daily');

//Authentication Routes Start

Route::get('/auth/callback','Auth\GsisAuthenticationController@callback');
Route::get('/login','Auth\GsisAuthenticationController@login');
Route::get('/register/{token}','Auth\GsisAuthenticationController@register');
Route::get('auth/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('auth/login', 'Auth\LoginController@login');
Route::get('auth/logout', 'Auth\LoginController@logout')->name('logout');

Route::get('auth/not-authorized','Auth\GsisAuthenticationController@notAuthorized')->name('not-authorized');
Route::get('auth/not-logged-in','Auth\GsisAuthenticationController@notLoggedIn')->name('not-logged-in');
//Authentication Routes End
//Users

Route::get('users', 'UsersController@index');
Route::post('users', 'UsersController@store');
Route::patch('users/{id}/local', 'UsersExtraController@updateLocalUser');
Route::patch('users/{id}/sso', 'UsersExtraController@updateSsoUser');
Route::post('users/change_state_to_sso', 'UsersExtraController@changeStateToSso');
Route::post('users/change_state_to_local', 'UsersExtraController@changeStateToLocal');
Route::post('users/resend_activation_email', 'UsersExtraController@resend_activation_email');
Route::patch('users/{id}', 'UsersController@update');
Route::post('users/{id}', 'UsersController@update');
Route::get('users/delete/{id}', 'UsersController@delete');
Route::post('users/delete_user', 'UsersController@delete_user');
Route::post('users/delete_anonymize/{id}','UsersController@delete_anonymize');
Route::post('users/disable_user', 'UsersController@disable_user');
Route::get('users/{id}/edit', 'UsersExtraController@edit');

//Manage extra emails by admins

Route::get('users/{id}/edit/emails', 'ExtraEmailsController@showManageEmailsFromAdmin');
Route::post('users/{id}/emails/add_new','ExtraEmailsController@addExtraMailFromAdmin');
Route::post('users/{id}/emails/deleteExtraEmail', 'ExtraEmailsController@deleteExtraMail');
Route::post('users/{id}/emails/makePrimary', 'ExtraEmailsController@makePrimary');
Route::post('users/{id}/emails/resend_extra_email_confirmation', 'ExtraEmailsController@resend_extra_email_confirmation');
Route::post('users/delete_user_image', 'UsersController@delete_user_image');
Route::get('administrators', 'UsersController@administrators');
Route::post('administrators/sendEmailToCoordinators', 'EmailsController@sendEmailToCoordinators');

Route::post('store_department_admin', 'UsersController@store_new_department_admin');
Route::post('store_institution_admin', 'UsersController@store_new_institution_admin');
Route::get('invite_department_admin/{user_id}','UsersController@invite_user_to_become_department_admin');

Route::post('users/store_new_conference_user', 'UsersController@store_new_conference_user');
Route::post('administrators', 'UsersController@admin_store');
Route::get('loginAs/{id}', 'UsersController@loginAs');

Route::get('message', function () {
    return view('message');
});

//Institutions
Route::get('institutions/{id}', 'InstitutionsController@show');
Route::get('institutions', 'InstitutionsController@index');
Route::post('institutions', 'InstitutionsController@store');
Route::patch('institutions/{id}', 'InstitutionsController@update');
//Disabled for now
//Route::get('institutions/delete/{id}', 'InstitutionsController@delete');
Route::get('institutions/{id}/edit', 'InstitutionsController@edit');
Route::get('institutions/departments/{id}', 'InstitutionsController@listDepartments');

Route::get('institutions/departments_with_other/{id}', 'InstitutionsController@listDepartmentsWrealOther');

Route::get('institutions/departments/other', 'InstitutionsController@listDepartmentsOtherOrg');

Route::get('institutions/adminDepartmentFromID/{id}', 'InstitutionsController@adminDepartmentFromID');
Route::get('institutions/loadDepartmentTable/{id}', 'InstitutionsController@loadDepartmentTable');
//Disabled for now
//Route::patch('institutions/{id}', 'InstitutionsController@update');

//Departments
Route::get('departments/{id}', 'DepartmentsController@show');
Route::get('departments', 'DepartmentsController@index');
Route::post('departments', 'DepartmentsController@store');
Route::patch('departments/{id}', 'DepartmentsController@update');
Route::get('departments/delete/{id}', 'DepartmentsController@delete');
Route::get('departments/{id}/edit', 'DepartmentsController@edit');
Route::patch('departments/{id}', 'DepartmentsController@update');
Route::get('institutions/{id}/departments', 'DepartmentsController@index');

//Conferences
Route::get('/conferences', 'ConferencesController@index');

Route::get('/conferences/get_active_conferences_container_ajax', 'ConferencesController@get_active_conferences_container_ajax');
Route::get('/conferences/get_future_conferences_container_ajax', 'ConferencesController@get_future_conferences_container_ajax');

Route::get('conferences/all', 'ConferencesController@all');

Route::get('conferences/date/{date}', 'ConferencesController@conferencesOnDate');
Route::get('conferences/ongoing', 'ConferencesController@ongoing');
Route::get('conferences/create', 'ConferencesController@create');
Route::post('conferences', 'ConferencesController@store');

//Test conferences

Route::get('test-conferences/create', 'ConferencesController@createTest');
Route::post('test-conferences', 'ConferencesController@storeTest');
Route::get('test-conferences/{id}/edit', 'ConferencesController@editTest');
Route::patch('test-conferences/{id}', 'ConferencesController@updateTest');

//End test conferences

Route::get('conferences/{id}', 'ConferencesController@show');
Route::get('conferences/{id}/copy', 'ConferencesController@copy');
Route::get('conferences/{id}/edit', 'ConferencesController@edit');
Route::post('conferences/sendParticipantEmail', 'ConferencesController@sendParticipantEmail');
Route::get('conferences/{id}/accept_invitation/{user_token}', 'ConferencesController@userAcceptInvitation');


Route::get('conferences/{id}/join_conference_mobile', 'ConferencesController@join_conference_mobile');
Route::get('conferences/SetEid', 'ConferencesController@SetEid');
Route::get('conferences/delete/{id}', 'ConferencesController@delete');
Route::patch('conferences/{id}', 'ConferencesController@update');
Route::get('conferences/{id}/details', 'ConferencesController@details');
Route::post('conferences/assign_participant', 'ConferencesController@assign_participant');
Route::post('conferences/assign_multiple_participants', 'ConferencesController@assign_multiple_participants');
Route::post('conferences/detach_participant', 'ConferencesController@detach_participant');
Route::get('conferences/settings', 'SettingsController@conferences_settings');
Route::post('conferences/settings', 'SettingsController@update_conferences_settings');
Route::post('conferences/conferenceUserDisconnect', 'ConferencesController@conferenceUserDisconnect');
Route::post('conferences/conferenceAddUserEmail', 'ConferencesController@conferenceAddUserEmail');
Route::get('conferences/requestParticipant/{email}', 'ConferencesController@requestParticipant');
Route::post('conferences/userConferenceDeviceAssign', 'ConferencesController@userConferenceDeviceAssign');
Route::get('conferences/{id}/joinConference', 'ConferencesController@joinConference');
Route::patch('conferences/{id}/inviteToConference', 'ConferencesController@inviteToConference');
Route::post('conferences/{id}/inviteH323ToConference', 'ConferencesController@inviteH323ToConference');
Route::get('conferences/{id}/joinVidyoMobile', 'ConferencesController@joinVidyoMobile');

Route::get('conferences/{id}/manage', 'ConferencesController@manage');
Route::get('/conferences/{id}/manage/get_participants_table_container_ajax', 'ConferencesController@get_participants_table_container_ajax');
Route::get('conferences/{id}/join_as_host', 'ConferencesController@join_as_host');

Route::get('/conferences/{conference_id}/retrieve_ip_address','ConferencesController@show_ip_retrieval_page');

Route::post('conferences/{id}/changeParticipantStatus', 'ConferencesController@changeParticipantStatus');
Route::get('conferences/{id}/conferenceConnection', 'ConferencesController@conferenceConnection');
Route::post('set_cookie','UsersController@set_cookie');
Route::post('conferences/{id}/lockUnlockRoom', 'ConferencesController@lockUnlockRoom');
Route::get('conferences/{id}/enableDisableRoom', 'ConferencesController@enableDisableRoom');

Route::post('conferences/{id}/disconnectConferenceAllParticipants', 'ConferencesController@disconnectConferenceAllParticipants');

Route::get('conferences/post_attendee','ConferencesController@post_attendee');

Route::get('terms', function () {
    return view('terms.terms');
})->name('terms');

Route::get('privacy_policy', function () {
    return view('terms.privacy_policy');
})->name('privacy-policy');

Route::post('/accept_terms_ajax', 'AccountController@accept_terms_ajax')->name('accept-terms-ajax');

//
//Route::get('sync_domains','ExtraEmailsController@SyncDomains');

//Settings

Route::get('settings', 'SettingsController@index');
Route::post('settings', 'SettingsController@update_application_settings');
Route::get('settings/notifyParticipants', 'SettingsController@notifyParticipants');


Route::get('/access_sso_login', 'ApplicationController@redirect_sso_login_to_account_application');


//Calendar
Route::get('calendar', function () {
    return view('calendar');
});

Route::get('calendar/json', 'ConferencesController@calendar_json');
Route::any('zoom_hooks','ZoomHooksController@listen');
