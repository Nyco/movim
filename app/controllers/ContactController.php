<?php

class ContactController extends BaseController {
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        $this->page->setTitle(__('page.contacts'));
    }
}
