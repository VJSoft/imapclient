<?php

namespace Vjsoft\Imapclient\Facades;

use Illuminate\Support\Facades\Facade;
use Vjsoft\Imapclient\Controllers\ImapClientPackageManager;

class Client extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ImapClientPackageManager::class;
    }
}