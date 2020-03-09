<?php

return [

    'host' => env('FIREWALL_HOST'),
    'username' => env('FIREWALL_USERNAME'),
    'protection'=>env('FIREWALL_PROTECTION','on'),
    'ssh_key'=>env('FIREWALL_SSH_KEY'),
    'open_for'=>300
];
