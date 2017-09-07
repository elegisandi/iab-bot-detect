<?php

namespace elegisandi\IABBotDetect;

use Illuminate\Support\Facades\Facade;

/**
 * Class IabFacade
 * @package elegisandi\IABBotDetect
 */
class IabFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'iab';
    }
}
