<?php

class BaseController {
    public $name = 'main';   // The name of the current page
    protected $session_only = false;// The page is protected by a session ?
    protected $raw = false;			// Display only the content ?
    protected $page;

    function __construct() {
        $this->loadLanguage();
        $this->page = new TplPageBuilder();
        $this->page->addScript('movim_hash.js');
        $this->page->addScript('movim_utils.js');
        $this->page->addScript('movim_base.js');
        $this->page->addScript('movim_tpl.js');
        $this->page->addScript('movim_rpc.js');
    }


    /**
     * Loads up the language, either from the User or default.
     */
    function loadLanguage() {
        $user = new User();
        if($user->isLogged()) {
            try{
                $lang = $user->getConfig('language');
                loadLanguage($lang);
            }
            catch(MovimException $e) {
                // Load default language.
                loadLanguage(Conf::getServerConfElement('defLang'));
            }
        }
        else if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            loadLanguageAuto();
        }
        else {
            loadLanguage(Conf::getServerConfElement('defLang'));
        }
    }

    /**
     * Returns the value of a $_GET variable. Mainly used to avoid getting
     * notices from PHP when attempting to fetch an empty variable.
     * @param name is the desired variable's name.
     * @return the value of the requested variable, or FALSE.
     */
    protected function fetchGet($name)
    {
        if(isset($_GET[$name])) {
            return htmlentities($_GET[$name]);
        } else {
            return false;
        }
    }

    /**
     * Returns the value of a $_POST variable. Mainly used to avoid getting
     * notices from PHP when attempting to fetch an empty variable.
     * @param name is the desired variable's name.
     * @return the value of the requested variable, or FALSE.
     */
    protected function fetchPost($name)
    {
        if(isset($_POST[$name])) {
            return htmlentities($_POST[$name]);
        } else {
            return false;
        }
    }

    function checkSession() {
        if($this->session_only) {
            $user = new User();

            if(!$user->isLogged()) {
                $this->name = 'login';
            }
        }
    }

    function display() {
        if($this->session_only) {
            $user = new User();
            $content = new TplPageBuilder($user);
        } else {
            $content = new TplPageBuilder();
        }

        if($this->raw) {
			echo $content->build($this->name.'.tpl');
			exit;
        } else {
			$built = $content->build($this->name.'.tpl');
			$this->page->setContent($built);
			echo $this->page->build('page.tpl');
		}
    }
}
