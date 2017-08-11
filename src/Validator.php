<?php

namespace elegisandi\IABBotDetect;

/**
 * Class Validator
 * @package elegisandi\IABBotDetect
 */
class Validator
{
    /**
     * @var
     */
    private $iab_user;

    /**
     * @var
     */
    private $iab_password;

    /**
     * @var string
     */
    private $iab_ftp = 'ftp://ftp.iab.net';

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
     * Validator constructor.
     * @param string|null $user_agent
     * @param array $credentials - IAB user and password
     */
    public function __construct($user_agent = null, array $credentials = [])
    {
        $this->setUserAgent($user_agent);
        $this->setCredentials($credentials);

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
        $this->iab_password = $credentials['password'] ?? null;
        $this->iab_user = $credentials['user'] ?? null;
    }

    /**
     * @param string|null $user_agent
     * @return bool
     */
    public function isValidBrowser($user_agent = null)
    {
        // initialize if not yet done
        if (!$this->initialized) {
            $this->initialize();
        }

        if (!is_null($user_agent)) {
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
     */
    public function isBot($user_agent = null, $white_list = true)
    {
        // initialize if not yet done
        if (!$this->initialized) {
            $this->initialize();
        }

        if (!is_null($user_agent)) {
            $this->setUserAgent($user_agent);
        }

        // return immediately if not whitelisted
        if ($white_list && !$this->isWhiteListed()) {
            return true;
        }

        $pattern = file_get_contents($this->blacklist_regex_cache);

        if (preg_match($pattern, $this->user_agent)) {
            // check for exceptions
            $exception_regex = file_get_contents($this->blacklist_exception_regex_cache);

            return !preg_match($exception_regex, $this->user_agent);
        }

        return false;
    }

    /**
     * @param bool $overwrite
     * @throws \Exception
     */
    public function initialize($overwrite = false)
    {
        if (empty($this->iab_user) && empty($this->iab_password)) {
            throw new \Exception('Invalid credentials.');
        }

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

        // get whitelist
        $this->whitelist_regex_cache = $cache_dir . DIRECTORY_SEPARATOR . 'iab_whitelist_regex.txt';

        if (!file_exists($this->whitelist_regex_cache) || $overwrite) {
            $white_list = $files_dir . DIRECTORY_SEPARATOR . $this->iab_whitelist_file;

            if (!file_exists($white_list) || $overwrite) {
                try {
                    // download whitelist file
                    `wget -q -O $white_list --user=$this->iab_user --password=$this->iab_password $this->iab_ftp/$this->iab_whitelist_file`;

                    // remove comments
                    `sed -i '/^#/d' $white_list`;
                } catch (\Exception $e) {
                    throw $e;
                }
            }

            $patterns = [];

            $list = new \SplFileObject($white_list);
            $list->setFlags(\SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);

            foreach ($list as $item) {
                $params = explode('|', $item);
                $pattern = preg_quote($params[0], '/');

                // skip if patter is inactive
                if ($params[1] == 0) {
                    continue;
                }

                // check if pattern occurs at the start of the UA string
                if ($params[2] == 1) {
                    $pattern = '^' . $pattern;
                }

                $patterns[] = $pattern;
            }

            file_put_contents($this->whitelist_regex_cache, '/(' . implode('|', $patterns) . ')/i');
            $list = null;
        }

        // get blacklist and exceptions
        $this->blacklist_regex_cache = $cache_dir . DIRECTORY_SEPARATOR . 'iab_blacklist_regex.txt';
        $this->blacklist_exception_regex_cache = $cache_dir . DIRECTORY_SEPARATOR . 'iab_blacklist_exception_regex.txt';

        if (!file_exists($this->blacklist_regex_cache) || $overwrite) {
            $black_list = $files_dir . DIRECTORY_SEPARATOR . $this->iab_blacklist_file;

            if (!file_exists($black_list) || $overwrite) {
                try {
                    // download blacklist file
                    `wget -q -O $black_list --user=$this->iab_user --password=$this->iab_password $this->iab_ftp/$this->iab_blacklist_file`;

                    // remove comments
                    `sed -i '/^#/d' $black_list`;
                } catch (\Exception $e) {
                    throw $e;
                }
            }

            $patterns = [];
            $exceptions = [];

            $list = new \SplFileObject($black_list);
            $list->setFlags(\SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);

            foreach ($list as $item) {
                $params = explode('|', $item);
                $pattern = preg_quote($params[0], '/');

                // skip if patter is inactive
                if ($params[1] == 0) {
                    continue;
                }

                if (!empty($params[2])) {
                    $exception = array_map(function ($pattern) {
                        return preg_quote(trim($pattern), '/');
                    }, explode(',', $params[2]));

                    $exceptions[] = '(' . implode('|', $exception) . ')';
                }

                // check if pattern occurs at the start of the UA string
                if ($params[5] == 1) {
                    $pattern = '^' . $pattern;
                }

                $patterns[] = $pattern;
            }

            file_put_contents($this->blacklist_regex_cache, '/(' . implode('|', $patterns) . ')/i');

            if (!empty($exceptions)) {
                file_put_contents($this->blacklist_exception_regex_cache, '/(' . implode('|', $exceptions) . ')/i');
            }

            $list = null;
        }

        $this->initialized = true;
    }
}
