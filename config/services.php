<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'zoom' => [
        'base_uri'=>env('ZOOM_BASE_URI','https://api.zoom.us'),
        'api_key'=>env('ZOOM_API_KEY'),
        'api_secret'=>env('ZOOM_API_SECRET'),
        'webhook_token'=>env('ZOOM_WEBHOOK_TOKEN'),
        'emea_ip_address'=>env('ZOOM_EMEA_IP_ADDRESS'),
        'h323_sensor_ip_address'=>env('ZOOM_H323_SENSOR_IP_ADDRESS'),
        'h323_disabled_group_id'=>env('ZOOM_H323_DISABLED_GROUP_ID'), //The id of the zoom group that h323 connections are disabled
    ],

    'gsis'=>[
        'clientId'=>env('GSIS_CLIENT_ID'),
        'clientSecret'=>env('GSIS_CLIENT_SECRET'),
        'redirectUri'=>env('GSIS_REDIRECT_URI'),
        'urlAuthorize'=>env('GSIS_USER_AUTHORIZATION_URL'),
        'urlAccessToken'=>env('GSIS_ACCESS_TOKEN_URL'),
        'urlResourceOwnerDetails'=>env('GSIS_RESOURCE_OWNER')
    ]

];
