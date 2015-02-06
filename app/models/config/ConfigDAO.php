<?php

namespace Modl;

class ConfigDAO extends SQL {
    function set(Config $c) {
        $this->_sql = '
            update config
            set environment   = :environment,  
                description   = :description, 
                theme         = :theme,
                locale        = :locale,
                maxusers      = :maxusers,
                loglevel      = :loglevel,
                timezone      = :timezone,
                websocketurl  = :websocketurl,
                xmppwhitelist = :xmppwhitelist,
                info          = :info,
                unregister    = :unregister,
                username      = :username,
                password      = :password,
                rewrite       = :rewrite,
                sizelimit     = :sizelimit';
        
        $this->prepare(
            'Config', 
            array(                
                'environment'  => $c->environment,
                'description'  => $c->description,
                'theme'        => $c->theme,
                'locale'       => $c->locale,
                'maxusers'     => $c->maxusers,
                'loglevel'     => $c->loglevel,
                'timezone'     => $c->timezone,
                'websocketurl' => $c->websocketurl,
                'xmppwhitelist'=> $c->xmppwhitelist,
                'info'         => $c->info,
                'unregister'   => $c->unregister,
                'username'     => $c->username,
                'password'     => $c->password,
                'rewrite'      => $c->rewrite,
                'sizelimit'    => $c->sizelimit
            )
        );
        
        $this->run('Config');
        
        if(!$this->_effective) {
            $this->_sql = '
                truncate table config;';

            $this->prepare(
                'Config', 
                array(
                )
            );
            
            $this->run('Config');
            
            $this->_sql = '
                insert into config
                (
                    environment,
                    description,
                    theme,
                    locale,
                    maxusers,
                    loglevel,
                    timezone,
                    websocketurl,
                    xmppwhitelist,
                    info,
                    unregister,
                    username,
                    password,
                    rewrite,
                    sizelimit
                )
                values
                (
                    :environment,
                    :description,
                    :theme,
                    :locale,
                    :maxusers,
                    :loglevel,
                    :timezone,
                    :websocketurl,
                    :xmppwhitelist,
                    :info,
                    :unregister,
                    :username,
                    :password,
                    :rewrite,
                    :sizelimit
                )
                ';
            
            $this->prepare(
                'Config', 
                array(
                    'environment'  => $c->environment,
                    'description'  => $c->description,
                    'theme'        => $c->theme,
                    'locale'       => $c->locale,
                    'maxusers'     => $c->maxusers,
                    'loglevel'     => $c->loglevel,
                    'timezone'     => $c->timezone,
                    'websocketurl' => $c->websocketurl,
                    'xmppwhitelist'=> $c->xmppwhitelist,
                    'info'         => $c->info,
                    'unregister'   => $c->unregister,
                    'username'     => $c->username,
                    'password'     => $c->password,
                    'rewrite'      => $c->rewrite,
                    'sizelimit'    => $c->sizelimit
                )
            );
            
            $this->run('Config');
        }
    }

    function get() {
        $this->_sql = '
            select * from config';

        $this->prepare('Config', array());
                
        $conf = $this->run('Config', 'item');

        if(!isset($conf))
            return new Config;

        return $conf;
    }
}
