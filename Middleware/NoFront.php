<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace Rdb\Modules\Nofront\Middleware;


/**
 * For disable front pages usage and redirect them all to /admin only.
 */
class NoFront
{


    /**
     * @var \Rdb\System\Config
     */
    protected $Config;


    /**
     * @var \Rdb\System\Container
     */
    protected $Container;


    /**
     * The class constructor.
     * 
     * @param \Rdb\System\Container $Container The DI container class.
     */
    public function __construct(\Rdb\System\Container $Container)
    {
        $this->Container = $Container;

        if ($Container->has('Config')) {
            $this->Config = $Container->get('Config');
            $this->Config->setModule('');
        } else {
            $this->Config = new \Rdb\System\Config();
        }
    }// __construct


    /**
     * Initialize to detect if root URL is not in exception or front pages URLs then redirect to admin.
     * 
     * @param string|null $response
     * @return string|null
     */
    public function init($response = '')
    {
        if (strtolower(PHP_SAPI) === 'cli') {
            // if running from CLI.
            // don't run this middleware here.
            return $response;
        }

        $allowedFirstURLSegment = ['languages', 'admin'];// all lower case.
        $Url = new \Rdb\System\Libraries\Url($this->Container);
        $firstURLSegment = strtolower($Url->getSegment(1));

        if (!in_array($firstURLSegment, $allowedFirstURLSegment)) {
            // if found first URL segment is not in allowed list.
            // redirect to /admin.
            // send no cache headers before.
            // https://stackoverflow.com/a/13640164/128761
            // https://stackoverflow.com/a/2068407/128761
            header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
            header('Pragma: no-cache'); // HTTP 1.0.
            header('Expires: 0'); // Proxies.

            // then redirect.
            $redirectUrl = $Url->getAppBasedPath(true) . '/admin';
            header('Location: ' . $redirectUrl);
            http_response_code(302);
            exit();
        }
        unset($allowedFirstURLSegment, $firstURLSegment, $Url);

        return $response;
    }// init


}
