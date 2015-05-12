<?php

use Respect\Validation\Validator;

class LoginAnonymous extends WidgetBase
{
    function load()
    {
        $this->registerEvent('session_start_handle', 'onStart');
    }

    function onStart($packet)
    {
        $session = \Sessionx::start();
        $session->load();

        if($session->mechanism == 'ANONYMOUS') {
            RPC::call('Rooms.anonymousJoin');
        }
    }

    function display()
    {

    }

    function ajaxLogin($username)
    {
        $validate_user = Validator::string()->length(4, 40);
        if(!$validate_user->validate($username)) {
            Notification::append(null, 'bad username');
            return;
        }
        
        // We get the Server Configuration
        $cd = new \Modl\ConfigDAO;
        $config = $cd->get();

        $host = 'anonymous.jappix.com';
        $password = 'AmISnowden?';

        // We try to get the domain
        $domain = \Moxl\Utils::getDomain($host);

        // We launch the XMPP socket
        RPC::call('register', $host);

        // We set the username in the session
        $s = Session::start();
        $s->set('username', $username);

        // We create a new session or clear the old one
        $s = Sessionx::start();
        $s->init($username, $password, $host, $domain);

        \Moxl\Stanza\Stream::init($host);
    }
}
