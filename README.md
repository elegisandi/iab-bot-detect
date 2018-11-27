# iab-bot-detect
Spider/Bot detection using IAB list

## Installation

    composer require elegisandi/iab-bot-detect

## Laravel/Lumen Integration

- Add IAB's service provider to your `config/app.php` providers

      elegisandi\IABBotDetect\IabServiceProvider::class

- Add IAB's facade to your `config/app.php` aliases

      'IAB' => elegisandi\IABBotDetect\IabFacade::class
      
- Set IAB credentials in your `.env` file

        IAB_USER=your-iab-user
        IAB_PASSWORD=your-iab-password

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
    $config = [
        'user' => 'YOUR_IAB_USER',
        'password' => 'YOUR_IAB_PASSWORD'
    ];
    
    try {
        $bot_detect = new Validator($user_agent, $config);
            
        if($bot_detect->isValidBrowser()) {
            echo 'valid browser';
        } else {
            echo 'invalid browser';
        }
    } catch (\Exception $e) {
        // fallback user agent validation
    }

**S3 Auto Backup**

_To enable this feature, you must add the following into the 2nd argument of the constructor_

    's3_backup' => true,
    's3_bucket' => 'YOUR_IAB_S3_BUCKET,
    's3_region' => 'YOUR_AWS_REGION',
    'aws_credentials' => [
        'key' => 'YOUR_AWS_ACCESS_KEY_ID',
        'secret' => 'YOUR_AWS_SECRET_ACCESS_KEY',
    ]

## Laravel/Lumen Usage

    <?php
    
    use IAB;
    
    $user_agent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
    
    try {
        if(IAB::isValidBrowser($user_agent)) {
            echo 'valid browser';
        } else {
            echo 'invalid browser';
        }
    } catch (\Exception $e) {
        // fallback user agent validation
    }
    
**For Lumen:**
    
    try {
        if(app('iab')->isValidBrowser($user_agent)) {
            echo 'valid browser';
        } else {
            echo 'invalid browser';
        }
    } catch (\Exception $e) {
        // fallback user agent validation
    }

**S3 Auto Backup**

_To enable this feature, you must set the following .env variables_

    IAB_S3_BACKUP=true
    IAB_S3_BUCKET=your-s3-bucket-name
    AWS_REGION=your-s3-region
    AWS_ACCESS_KEY_ID=your-aws-access-key
    AWS_SECRET_ACCESS_KEY=your-aws-secret-key

## Methods

- #### setUserAgent($user_agent)
    > where **`$user_agent`** = User Agent string
    
- #### setCredentials($credentials)
    > where **`$credentials`** = a key-pair values array of your IAB user and password
    
- #### isValidBrowser($user_agent)
    > where **`$user_agent`** could be null
    
    > throws an _error exception_
    
    Returns **`boolean`**
    
- #### isBot($user_agent)
    > where **`$user_agent`** could be null
    
    > throws an _error exception_
    
    Returns **`boolean`**
    
- #### initialize($overwrite)
    > where **`$overwrite`** = a `boolean` flag to overwrite cache files (_default value_: `false`)
    
    > throws an _error exception_
    
    Prepares cache files needed for bot detection process.
    
## Error Exceptions

- ###### IABRequestException
- ###### IABBackupException
- ###### InvalidIABCacheException
- ###### InvalidIABCredentialsException

## Contributing

Open an issue first to discuss potential changes/additions.

## License

[MIT](https://github.com/elegisandi/iab-bot-detect/blob/master/LICENSE)
