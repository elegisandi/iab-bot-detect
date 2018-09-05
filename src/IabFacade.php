<?php

namespace elegisandi\IABBotDetect;

use Illuminate\Support\Facades\Facade;

/**
 * Class IabFacade
 * @package elegisandi\IABBotDetect
 *
 * @method static void setUserAgent($user_agent = null)
 * @method static void setCredentials(array $credentials)
 * @method static bool isValidBrowser($user_agent = null)
 * @method static bool isBot($user_agent = null, $white_list = true)
 * @method static void initialize($overwrite = false)
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
