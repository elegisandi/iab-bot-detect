# iab-bot-detect
Spider/Bot detection using IAB list

## Installation

    composer require elegisandi/iab-bot-detect:dev-master

## Laravel/Lumen Integration

- Add IAB's service provider to your `config/app.php` providers

      elegisandi\IABBotDetect\IabServiceProvider::class

- Add IAB's facade to your `config/app.php` aliases

      'IAB' => elegisandi\IABBotDetect\IabFacade::class
      
- Set IAB credentials in your `.env` file

        IAB_USER=YOUR_IAB_USER
        IAB_PASSWORD=YOUR_IAB_PASSWORD
        
**If you want to modify the package config, just run:**

    php artisan vendor:publish --provider=elegisandi\\IABBotDetect\\IabServiceProvider

**For Lumen:**

- Register IAB's service provider to your `bootstrap/app.php`

      $app->register(elegisandi\IABBotDetect\IabServiceProvider::class);      

## Configuration

- Generate whitelist and blacklist cache

      php artisan iab:refresh-list

    > You may add the option **`--overwrite`** to reset cache.

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

## Laravel/Lumen Usage

    <?php
    
    use IAB;
    
    $user_agent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
    
    if(IAB::isValidBrowser($user_agent)) {
        echo 'valid browser';
    } else {
        echo 'invalid browser';
    }
    
**For Lumen:**
    
    if(app('iab')->isValidBrowser($user_agent)) {
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
