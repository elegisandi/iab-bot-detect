# iab-bot-detect
Spider/Bot detection using IAB list

## Installation

    composer require elegisandi/iab-bot-detect

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
    > where **`$user_agent`** = User Agent string
    
- #### setCredentials($credentials)
    > where **`$credentials`** = a key-pair values array of your IAB user and password
    
- #### isValidBrowser($user_agent)
    > where **`$user_agent`** could be null
    
    Returns **`boolean`**
    
- #### isBot($user_agent)
    > where **`$user_agent`** could be null
    
    Returns **`boolean`**
    
- #### initialize($overwrite)
    > where **`$overwrite`** = a `boolean` flag to overwrite cache files (_default value_: `false`)
    
    Prepares cache files needed for bot detection process.

## Contributing

Open an issue first to discuss potential changes/additions.

## License

[MIT](https://github.com/elegisandi/iab-bot-detect/blob/master/LICENSE)
