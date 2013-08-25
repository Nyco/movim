<?php
if (!defined('DOCUMENT_ROOT')) die('Access denied');

/**
 * First thing, define autoloader
 * @param string $className
 * @return boolean
 */
function __autoload($className)
{
    $className = ltrim($className, '\\');
    $fileName  = DOCUMENT_ROOT;
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= '/'.str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
    if (file_exists($fileName)) {
        require_once( $fileName);
        return true;
    } else  {
        return false;
    }
}

/**
 * Error Handler...
 */
function systemErrorHandler ( $errno , $errstr , $errfile ,  $errline , $errcontext=null ) 
{
    \system\Logs\Logger::addLog( $errstr,$errno,'system',$errfile,$errline);
    return false;
}

/**
 * Manage boot order
 */
class Bootstrap {
    function boot() {
        mb_internal_encoding("UTF-8");
        //First thing to do, define error management (in case of error forward)
        $this->setLogs();
        //define all needed constants
        $this->setContants();
        //Check if vital system need is OK
        $this->checkSystem();
        
        
        $this->setBrowserSupport();
        
        $this->loadSystem();
        $this->loadCommonLibraries();
        $this->loadDispatcher();
        
        $this->setTimezone();
        
        
        $loadmodlsuccess = $this->loadModl();
        ;
        $this->loadMoxl();
        
        if($loadmodlsuccess) {
            $this->startingSession();
        } else {
            throw new Exception('Error loading Modl');
        }
    }
    private function checkSystem() {
        $listWritableFile = array(
            DOCUMENT_ROOT.'/log/logger.log',
            DOCUMENT_ROOT.'/log/php.log',
            DOCUMENT_ROOT.'/cache/test.tmp',
        );
        $errors=array();
        foreach($listWritableFile as $fileName) {
            if (!file_exists($fileName)) {
                if (touch($fileName) !== true) {
                    $errors[] = 'We\'re unable to write to '.$fileName.': check rights';
                } 
            }else if (is_writable($fileName) !== true) {
                $errors[] = 'We\'re unable to write to file '.$fileName.': check rights';
            }
        }
        if (!function_exists('json_decode')) {
             $errors[] = 'You need to install php5-json that\'s not seems to be installed';
        }
        if (count($errors)) {
            throw new Exception(implode("\n<br />",$errors));
        }
    }
    private function setContants() {
        define('APP_TITLE',     'Movim');
        define('APP_NAME',      'movim');
        define('APP_VERSION',   $this->getVersion());
        define('BASE_URI',      $this->getBaseUri());
        
        define('THEMES_PATH',   DOCUMENT_ROOT . '/themes/');
        define('USERS_PATH',    DOCUMENT_ROOT . '/users/');
        define('APP_PATH',      DOCUMENT_ROOT . '/app/');
        define('SYSTEM_PATH',   DOCUMENT_ROOT . '/system/');
        define('LIB_PATH',      DOCUMENT_ROOT . '/lib/');
        define('LOCALES_PATH',  DOCUMENT_ROOT . '/locales/');
        define('CACHE_PATH',    DOCUMENT_ROOT . '/cache/');
        
        if (!defined('DOCTYPE')) {
            define('DOCTYPE','text/html');
        }
    }

    private function getVersion() {
        $file = "VERSION";
        if($f = fopen(DOCUMENT_ROOT.'/'.$file, 'r')) {
            return trim(fgets($f));
        }
    }
    
    private function getBaseUri() {
        $path = dirname($_SERVER['PHP_SELF']).'/';
        // Determining the protocol to use.
        $uri = "http://";
        if((
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "") 
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == "https")) {
            $uri = 'https://';
        }

        if($path == "") {
            $uri .= $_SERVER['HTTP_HOST'] ;
        } else {
            $uri .= $_SERVER['HTTP_HOST'] . $path;
        }

        $uri = str_replace('jajax.php', '', $uri);
        
        return $uri;
    }
    
    private function loadSystem() {
        // Loads up all system libraries.
        require_once(SYSTEM_PATH . "/i18n/i18n.php");

        require_once(SYSTEM_PATH . "Session.php");
        require_once(SYSTEM_PATH . "Utils.php");
        require_once(SYSTEM_PATH . "UtilsPicture.php");
        require_once(SYSTEM_PATH . "Cache.php");
        require_once(SYSTEM_PATH . "Event.php");
        require_once(SYSTEM_PATH . "MovimException.php");
        require_once(SYSTEM_PATH . "RPC.php");
        require_once(SYSTEM_PATH . "User.php");
    }
    
    private function loadCommonLibraries() {
        // XMPPtoForm lib
        require_once(LIB_PATH . "XMPPtoForm.php");

        // Markdown lib
        require_once(LIB_PATH . "Markdown.php");
        
        // The template lib
        require_once(LIB_PATH . 'RainTPL.php');
    }
    
    private function loadDispatcher() {
        require_once(SYSTEM_PATH . "controllers/ControllerBase.php");
        require_once(SYSTEM_PATH . "controllers/ControllerMain.php");
        require_once(SYSTEM_PATH . "controllers/ControllerAjax.php");
        //require_once(SYSTEM_PATH . "controllers/FrontController.php");

        require_once(SYSTEM_PATH . "Route.php");

        require_once(SYSTEM_PATH . "template/TplPageBuilder.php");

        require_once(SYSTEM_PATH . "widget/WidgetBase.php");
        require_once(SYSTEM_PATH . "widget/WidgetWrapper.php");

        require_once(APP_PATH . "widgets/WidgetCommon/WidgetCommon.php");
        require_once(APP_PATH . "widgets/Notification/Notification.php");
    }
    
    private function setLogs() {
        try {
            define('ENVIRONMENT',\system\Conf::getServerConfElement('environment'));
        } catch (Exception $e) {
            define('ENVIRONMENT','development');//default environment is production
        }
        define('LOG_MANAGEMENT','log_folder');//'error_log' || 'log_folder' || 'syslog'
        if (ENVIRONMENT === 'development') {
            ini_set('log_errors', 1);
            ini_set('display_errors', 0);
            ini_set('error_reporting', E_ALL );
            
        } else {
            ini_set('log_errors', 1);
            ini_set('display_errors', 0);
            ini_set('error_reporting', E_ALL ^ E_DEPRECATED ^ E_NOTICE);
        }
        if (LOG_MANAGEMENT === 'log_folder') {
            ini_set('error_log', DOCUMENT_ROOT.'/log/php.log');
        }
        set_error_handler('systemErrorHandler', E_ALL);
    }
    
    private function setTimezone() {
        // We set the default timezone to the server timezone
        $conf = \system\Conf::getServerConf();
        if(isset($conf['timezone']))
            date_default_timezone_set($conf['timezone']);
    }
    
    private function loadModl() {
        // We load Movim Data Layer
        require_once(LIB_PATH . 'Modl/loader.php');
        
        $db = modl\Modl::getInstance();
        $db->setConnectionArray(\System\Conf::getServerConf());
        
        return $db->testConnection();
    }
    
    private function loadMoxl() {
        // We load Movim XMPP Library
        require_once(LIB_PATH . 'Moxl/loader.php');
    }
    
    private function setBrowserSupport() {
        if(isset( $_SERVER['HTTP_USER_AGENT'])) {
            $useragent = $_SERVER['HTTP_USER_AGENT'];

            if (preg_match('|MSIE ([0-9].[0-9]{1,2})|',$useragent,$matched)) {
                $browser_version=$matched[1];
                $browser = 'IE';
            } elseif (preg_match('/Opera[\/ ]([0-9]{1}\.[0-9]{1}([0-9])?)/',$useragent,$matched)) {
                $browser_version=$matched[1];
                $browser = 'Opera';
            } elseif(preg_match('|Firefox/([0-9\.]+)|',$useragent,$matched)) {
                $browser_version=$matched[1];
                $browser = 'Firefox';
            } elseif(preg_match('|Safari/([0-9\.]+)|',$useragent,$matched)) {
                $browser_version=$matched[1];
                $browser = 'Safari';
            }
        } else {
            $browser_version = 0;
            $browser= 'other';
        }

        define('BROWSER_VERSION', $browser_version);
        define('BROWSER', $browser);

        $compatible = false;

        switch($browser) {
            case 'Firefox':
                if($browser_version > 3.5)
                    $compatible = true;
            break;
            case 'IE':
                if($browser_version > 9.0)
                    $compatible = true;
            break;
            case 'Safari': // Also Chrome-Chromium
                if($browser_version > 522.0)
                    $compatible = true;
            break;
            case 'Opera':
                if($browser_version > 9.0)
                    $compatible = true;
            break;
        }

        define('BROWSER_COMP', $compatible);
    }
    
    private function startingSession() {
        global $session;
        // Starting session.
        $sess = Session::start(APP_NAME);
        $session = $sess->get('session');
    }
}
