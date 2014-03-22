<?php 
class Route extends \BaseController {
    public $_routes;
    
    public function __construct() {
        $this->_routes = array(
                'account'       => false,
                'accountnext'   => array('s', 'err'),
                'visio'         => false,
                'main'          => false,
                'news'          => false,
                'loading'       => false,
                'admin'         => false,
                'explore'       => false,
                'discover'      => false,
                'profile'       => false,
                'media'         => array('f'),
                'conf'          => false,
                'help'          => false,
                'about'         => false,
                'login'         => array('err'),
                'disconnect'    => array('err'),
                'friend'        => array('f'),
                'blog'          => array('f', 'n'),
                'feed'          => array('f', 'n'),
                'nodeconfig'    => array('s', 'n'),
                'node'          => array('s', 'n'),
                'server'        => array('s'),
            );

        if(isset($_SERVER['HTTP_MOD_REWRITE']) && $_SERVER['HTTP_MOD_REWRITE']) {
            $q = $this->fetchGet('query');
            //load data in $_GET variable
            if (!$this->find($q)) {
                $this->error404();
                exit;
            }
        } else {
            $q = $this->fetchGet('q');
            if(empty($q))
                $_GET['q'] = 'main';
        }
    }
    
    public function find($q) {
        // We decompose the URL
        $request = explode('/', $q);
                
        if(empty($q)) {
            $_GET['q'] = 'main';
            return true;
        } elseif (isset($this->_routes[$request[0]])) {
            // And we search a pattern for the current page
            $route = $this->_routes[$request[0]];
            
            
            $_GET['q'] = $request[0];
            array_shift($request);
            // If we find it we see if it's a simple page (with no GET)
            //check if something else to get...
            if(count($request) ) {
                $i = 0;
                foreach($route as $key) {
                    if (isset($request[$i])) {
                        $_GET[$key] = $request[$i];
                    } 
                    $i++;
                }
            }
            return true;
        } else
            return false;
    }
    
    public static function urlize($page, $params = false, $tab = false) {
        $r = new Route();
        $routes = $r->_routes;
        
        if(isset($routes[$page])) {        
            if($params != false && count($routes[$page]) != count($params)) 
                \system\Logs\Logger::log(t('Route error, please set all the parameters for the page %s', $page));
            else {
                if($tab != false)
                    $tab = '#'.$tab;
                //We construct a classic URL if the rewriting is disabled
                if(!isset($_SERVER['HTTP_MOD_REWRITE']) || !$_SERVER['HTTP_MOD_REWRITE']) {
                    $uri = BASE_URI.'?q='.$page;
                    
                    if($params != false && is_array($params)) {
                        $i = 0;
                        foreach($params as $value) {
                            $uri .= '&'.$routes[$page][$i].'='.$value;
                            $i++;
                        }
                    }
                    elseif($params != false)
                        $uri .= '&'.$routes[$page][0].'='.$params;
                } 
                // Here we got a beautiful rewriten URL !
                else {
                    $uri = BASE_URI.$page;
                    if($params != false && is_array($params))
                        foreach($params as $value)
                            $uri .= '/'.$value;
                    elseif($params != false)
                        $uri .= '/'.$params;
                }
                return $uri.$tab;
            }
        } else
            \system\Logs\Logger::log(t('Route not set for the page %s', $page));
    }
}
