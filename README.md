# iab-bot-detect
Spider/Bot detection using IAB list

## Basic Usage

    <?php
    
    use elegisandi\IABBotDetect\Validator;
    
    $user_agent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
    $credentials = [
        'user' => 'YOUR_IAB_USER',
        'password' => 'YOUR_IAB_PASSWORD'
    ];
    
    $bot_detect = new Validator($user_agent, $credentials);
    
    if($bot_detect->isValidBrowser()) {
        echo 'valid browser';
    } else {
        echo 'invalid browser';
    }
   
## Methods

- #### setUserAgent($user_agent)
    > where `$user_agent` = User Agent String
    
- #### setCredentials($credentials)
    > where `$credentials` = an array of your IAB user and password
    
- #### isValidBrowser($user_agent)
    > where `$user_agent` = User Agent String
    
    Returns **`boolean`** 
    
- #### isBot($user_agent)
    > where `$user_agent` = User Agent String
    
    Returns **`boolean`** 