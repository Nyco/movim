<?php

class LoginController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        $this->page->setTitle(__('title.login', APP_TITLE));
        
        $this->page->menuAddLink(__('page.home'), 'main', true);
        $this->page->menuAddLink(__('page.discover'), 'discover');
        $this->page->menuAddLink(__('page.about'), 'about');
    }
}
