<?php

/**
 * @file User.php
 * This file is part of Movim.
 *
 * @brief Handles the user's login and user.
 *
 * @author Jaussoin Timothée
 *
 * @date 2014
 *
 * Copyright (C)2014 Movim
 *
 * See COPYING for licensing information.
 */
class User {
    private $xmppSession;

    public $username = '';
    private $password = '';
    private $config = array();
    
    public $userdir;
    public $useruri;
    
    public $sizelimit;

    /**
     * Class constructor. Reloads the user's session or attempts to authenticate
     * the user.
     * Note that the constructor is private. This class is a singleton.
     */
    function __construct()
    {
        if($this->isLogged()) {
            $session = \Sessionx::start();
    
            $this->username = $session->user.'@'.$session->host;
            
            if($session->config)
                $this->config = $session->config;

            $this->sizelimit = (int)\system\Conf::getServerConfElement('sizeLimit');

            $this->userdir = DOCUMENT_ROOT.'/users/'.$this->username.'/';
            $this->useruri = BASE_URI.'users/'.$this->username.'/';
        }
    }
    
    /**
     * Get the current size in bytes of the user directory
     */
    function dirSize()
    {
        $sum = 0;

        foreach($this->getDir() as $s)
            $sum = $sum + filesize($this->userdir.$s);
        
        return $sum;
    }
    
    /**
     * Get a list of the files in the user dir with uri, dir and thumbs
     */
    function getDir()
    {
        $dir = array();
        if(is_dir($this->userdir))
            foreach(scandir($this->userdir) as $s) {
                if(
                    $s != '.' && 
                    $s != '..' && 
                    //substr($s, 0, 6) != 'thumb_' &&
                    //substr($s, 0, 7) != 'medium_' && 
                    $s != 'index.html') {
                    
                    /*$file = array(
                        'uri'       => $this->useruri.$s,
                        'dir'       => $this->userdir.$s,
                        'thumb'    => $this->useruri.'thumb_'.$s,
                        'medium'   => $this->useruri.'medium_'.$s);
                    $dir[$s] = $file;*/
                    array_push($dir, $s);
                }
            }
        
        return $dir;
    }

    /**
     * Checks if the user has an open session.
     */
    function isLogged()
    {
        // User is not logged in if both the session vars and the members are unset.
        $session = \Sessionx::start();

        if($session->active)
            return $session->active;
        else
            return false;
    }

    function desauth()
    {
        $pd = new modl\PresenceDAO();
        $pd->clearPresence($this->username);

        $s = \Sessionx::start();
        $s->destroy();
            
        $sess = Session::start(APP_NAME);
        Session::dispose(APP_NAME);
    }

    function getLogin()
    {
        return $this->username;
    }

    function getPass()
    {
        return $this->password;
    }

    function setConfig(array $config)
    {
        $session = \Sessionx::start();
        $session->config = $config;
    }

    function getConfig($key = false)
    {
        if($key == false)
            return $this->config;
        if(isset($this->config[$key]))
            return $this->config[$key];
    }

}
