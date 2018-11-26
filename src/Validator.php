<?php

namespace elegisandi\IABBotDetect;

use elegisandi\IABBotDetect\Exceptions\InvalidIABCacheException;
use elegisandi\IABBotDetect\Exceptions\InvalidIABCredentialsException;
use elegisandi\IABBotDetect\Exceptions\IABRequestException;
use Exception;

/**
 * Class Validator
 * @package elegisandi\IABBotDetect
 */
class Validator
{
    /**
     * @var mixed
     */
    private $iab_user = null;

    /**
     * @var mixed
     */
    private $iab_password = null;

    /**
     * @var string
     */
    private $iab_ftp = 'ftp.iab.net';

    /**
     * @var string
     */
    private $iab_whitelist_file = 'IAB_ABC_International_List_of_Valid_Browsers.txt';

    /**
     * @var string
     */
    private $iab_blacklist_file = 'IAB_ABC_International_Spiders_and_Robots.txt';

    /**
     * @var
     */
    protected $whitelist_regex_cache;

    /**
     * @var
     */
    protected $blacklist_regex_cache;

    /**
     * @var
     */
    protected $blacklist_exception_regex_cache;

    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * @var
     */
    public $user_agent;

    /**
     * @var bool
     */
    public $s3_backup = false;

    /**
     * @var mixed
     */
    protected $s3_bucket = null;

    /**
     * @var mixed
     */
    protected $s3_region = null;

    /**
     * @var array
     */
    protected $aws_credentials = [];

    /**
     * @var bool
     */
    private $whitelist_cache_created = false;

    /**
     * @var bool
     */
    private $blacklist_cache_created = false;

    /**
     * Validator constructor.
     * @param string|null $user_agent
     * @param array $config
     * @throws Exception
     */
    public function __construct($user_agent = null, array $config = [])
    {
        $this->setUserAgent($user_agent);
        $this->setCredentials($config);

        // should enable s3 backup
        if ($this->s3_backup = !empty($config['s3_backup'])) {
            if (!empty($config['s3_bucket'])) {
                $this->s3_bucket = $config['s3_bucket'];
            }

            if (!empty($config['s3_region'])) {
                $this->s3_region = $config['s3_region'];
            }

            if (!empty($config['aws_credentials'])) {
                $this->aws_credentials = $config['aws_credentials'];
            }
        }

        if (!empty($this->iab_user) && !empty($this->iab_password)) {
            $this->initialize();
        }
    }

    /**
     * @param string|null $user_agent
     */
    public function setUserAgent($user_agent = null)
    {
        $this->user_agent = $user_agent;
    }

    /**
     * @param array $credentials
     */
    public function setCredentials(array $credentials)
    {
        if (!empty($credentials['user'])) {
            $this->iab_user = $credentials['user'];
        }

        if (!empty($credentials['password'])) {
            $this->iab_password = $credentials['password'];
        }
    }

    /**
     * @param string|null $user_agent
     * @return bool
     * @throws Exception
     */
    public function isValidBrowser($user_agent = null)
    {
        // initialize if not yet done
        if (!$this->initialized) {
            $this->initialize();
        }

        if (!empty($user_agent)) {
            $this->setUserAgent($user_agent);
        }

        return $this->isWhiteListed() && !$this->isBot(null, false);
    }

    /**
     * @return int
     */
    private function isWhiteListed()
    {
        $pattern = file_get_contents($this->whitelist_regex_cache);

        return preg_match($pattern, $this->user_agent);
    }

    /**
     * @param string|null $user_agent
     * @param bool $white_list
     * @return bool
     * @throws Exception
     */
    public function isBot($user_agent = null, $white_list = true)
    {
        // initialize if not yet done
        if (!$this->initialized) {
            $this->initialize();
        }

        if (!empty($user_agent)) {
            $this->setUserAgent($user_agent);
        }

        // return immediately if not whitelisted
        if ($white_list && !$this->isWhiteListed()) {
            return true;
        }

        $pattern = file_get_contents($this->blacklist_regex_cache);

        if (preg_match($pattern, $this->user_agent)) {

            if (!file_exists($this->blacklist_exception_regex_cache)) {
                return true;
            }

            // check for exceptions
            $exception_regex = file_get_contents($this->blacklist_exception_regex_cache);

            return !preg_match($exception_regex, $this->user_agent);
        }

        return false;
    }

    /**
     * @param bool $overwrite
     * @throws Exception
     */
    public function initialize($overwrite = false)
    {
        $root_dir = dirname(__DIR__);

        // cache dir
        $cache_dir = $root_dir . DIRECTORY_SEPARATOR . 'cache';

        // files dir
        $files_dir = $root_dir . DIRECTORY_SEPARATOR . 'files';

        // create cache dir if not existing
        if (!file_exists($cache_dir)) {
            mkdir($cache_dir);
        }

        // create files dir if not existing
        if (!file_exists($files_dir)) {
            mkdir($files_dir);
        }

        // check if has iab login credentials
        if (empty($this->iab_user) && empty($this->iab_password)) {
            throw new InvalidIABCredentialsException('IAB account\'s username and password are not set.');
        }

        // get whitelist
        $this->whitelist_regex_cache = $cache_dir . DIRECTORY_SEPARATOR . 'iab_whitelist_regex.txt';

        if ((!file_exists($this->whitelist_regex_cache) || $overwrite) && !$this->whitelist_cache_created) {
            $white_list = $files_dir . DIRECTORY_SEPARATOR . $this->iab_whitelist_file;

            $this->createWhiteListCache($white_list, $overwrite);
        }

        // get blacklist and exceptions
        $this->blacklist_regex_cache = $cache_dir . DIRECTORY_SEPARATOR . 'iab_blacklist_regex.txt';
        $this->blacklist_exception_regex_cache = $cache_dir . DIRECTORY_SEPARATOR . 'iab_blacklist_exception_regex.txt';

        if ((!file_exists($this->blacklist_regex_cache) || $overwrite) && !$this->blacklist_cache_created) {
            $black_list = $files_dir . DIRECTORY_SEPARATOR . $this->iab_blacklist_file;

            $this->createBlackListCache($black_list, $overwrite);
        }

        $this->initialized = true;
    }

    /**
     * @param string $file_path
     * @param bool $overwrite
     * @throws Exception
     */
    private function createWhiteListCache($file_path, $overwrite)
    {
        if (!file_exists($file_path) || $overwrite) {
            try {
                $this->storeIABFile($this->iab_whitelist_file, $file_path);
            } catch (IABRequestException $exception) {
                if ($this->s3_backup) {
                    $this->fetchS3BackupFile($this->iab_whitelist_file, $file_path);
                } else {
                    throw $exception;
                }
            } catch (Exception $exception) {
                if ($this->s3_backup) {
                    $this->fetchS3BackupFile($this->iab_whitelist_file, $file_path);
                } else {
                    throw new IABRequestException("Error downloading {$this->iab_whitelist_file} file.", 0, $exception);
                }
            }
        }

        $patterns = [];

        $list = new \SplFileObject($file_path);
        $list->setFlags(\SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);

        foreach ($list as $item) {
            $params = explode('|', $item);
            $pattern = preg_quote($params[0], '/');

            // skip if pattern is inactive
            if (isset($params[1]) && $params[1] == 0) {
                continue;
            }

            // check if pattern occurs at the start of the UA string
            if (isset($params[2]) && $params[2] == 1) {
                $pattern = '^' . $pattern;
            }

            $patterns[] = $pattern;
        }

        if (empty($patterns)) {
            throw new InvalidIABCacheException('IAB whitelist cache is empty.');
        }

        file_put_contents($this->whitelist_regex_cache, '/(' . implode('|', $patterns) . ')/i');

        $list = null;
        $this->whitelist_cache_created = true;
    }

    /**
     * @param string $file_path
     * @param bool $overwrite
     * @throws Exception
     */
    private function createBlackListCache($file_path, $overwrite)
    {
        if (!file_exists($file_path) || $overwrite) {
            try {
                $this->storeIABFile($this->iab_blacklist_file, $file_path);
            } catch (IABRequestException $exception) {
                if ($this->s3_backup) {
                    $this->fetchS3BackupFile($this->iab_blacklist_file, $file_path);
                } else {
                    throw $exception;
                }
            } catch (Exception $exception) {
                if ($this->s3_backup) {
                    $this->fetchS3BackupFile($this->iab_blacklist_file, $file_path);
                } else {
                    throw new IABRequestException("Error downloading {$this->iab_blacklist_file} file.", 0, $exception);
                }
            }
        }

        $patterns = [];
        $exceptions = [];

        $list = new \SplFileObject($file_path);
        $list->setFlags(\SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);

        foreach ($list as $item) {
            $params = explode('|', $item);
            $pattern = preg_quote($params[0], '/');

            // skip if pattern is inactive
            if (isset($params[1]) && $params[1] == 0) {
                continue;
            }

            if (!empty($params[2])) {
                $exception = array_map(function ($pattern) {
                    return preg_quote(trim($pattern), '/');
                }, explode(',', $params[2]));

                $exceptions[] = '(' . implode('|', $exception) . ')';
            }

            // check if pattern occurs at the start of the UA string
            if (isset($params[5]) && $params[5] == 1) {
                $pattern = '^' . $pattern;
            }

            $patterns[] = $pattern;
        }

        if (empty($patterns)) {
            throw new InvalidIABCacheException('IAB blacklist cache is empty.');
        }

        file_put_contents($this->blacklist_regex_cache, '/(' . implode('|', $patterns) . ')/i');

        if (!empty($exceptions)) {
            file_put_contents($this->blacklist_exception_regex_cache, '/(' . implode('|', $exceptions) . ')/i');
        }

        $list = null;
        $this->blacklist_cache_created = true;
    }

    /**
     * @param string $filename
     * @param string $filepath
     * @throws IABRequestException
     */
    private function storeIABFile($filename, $filepath)
    {
        // set up ftp basic connection
        $conn_id = ftp_connect($this->iab_ftp);

        // login with username and password
        ftp_login($conn_id, $this->iab_user, $this->iab_password);

        // turn passive mode on
        ftp_pasv($conn_id, true);

        // download whitelist file
        if (!ftp_get($conn_id, $filepath, $filename, FTP_BINARY)) {
            throw new IABRequestException("Error downloading {$filename} file from IAB's FTP server.");
        }

        // close the connection
        ftp_close($conn_id);

        // remove comments
        `sed -i '/^#/d' $filepath`;

        // s3 backup
        if ($this->s3_backup) {
            $this->generateS3Client()->putObject([
                'Bucket' => $this->s3_bucket,
                'Body' => file_get_contents($filepath),
                'Key' => $filename,
            ]);
        }
    }

    /**
     * @param string $filename
     * @param string $filepath
     * @throws IABRequestException
     */
    private function fetchS3BackupFile($filename, $filepath)
    {
        try {
            $this->generateS3Client()->getObject([
                'Bucket' => $this->s3_bucket,
                'Key' => $filename,
                'SaveAs' => $filepath,
            ]);
        } catch (Exception $e) {
            throw new IABRequestException("Error downloading {$filename} file from Amazon S3.", 0, $e);
        }
    }

    /**
     * @return \Aws\S3\S3Client
     */
    private function generateS3Client()
    {
        return new \Aws\S3\S3Client([
            'version' => 'latest',
            'region' => $this->s3_region,
            'credentials' => new \Aws\Credentials\Credentials($this->aws_credentials['key'], $this->aws_credentials['secret'])
        ]);
    }
}
