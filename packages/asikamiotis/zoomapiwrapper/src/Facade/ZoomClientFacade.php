<?php

namespace Asikamiotis\ZoomApiWrapper;

use Illuminate\Support\Facades\Facade;

class ZoomClientFacade extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'zoom-client';
    }
}
