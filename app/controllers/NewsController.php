<?php

class NewsController extends BaseController {
    function load() {
        $this->session_only = true;
    }

    function dispatch() {
        $this->page->setTitle(__('title.news', APP_TITLE));
    }
}
