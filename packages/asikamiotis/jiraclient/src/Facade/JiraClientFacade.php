<?php

namespace Asikamiotis\ZoomApiWrapper;

use Illuminate\Support\Facades\Facade;

class JiraClientFacade extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'jira-client';
    }
}
