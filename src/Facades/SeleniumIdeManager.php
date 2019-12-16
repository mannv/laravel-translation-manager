<?php

namespace Plum\SeleniumIdeManager\Facades;

use Illuminate\Support\Facades\Facade;

class SeleniumIdeManager extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'seleniumidemanager';
    }
}
