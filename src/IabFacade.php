<?php

namespace elegisandi\IABBotDetect;

use Illuminate\Support\Facades\Facade;

/**
 * Class IabFacade
 * @package elegisandi\IABBotDetect
 *
 * @method static Validator setUserAgent($user_agent = null)
 * @method static Validator setCredentials(array $credentials)
 * @method static Validator isValidBrowser($user_agent = null)
 * @method static Validator isBot($user_agent = null, $white_list = true)
 * @method static Validator initialize($overwrite = false)
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
