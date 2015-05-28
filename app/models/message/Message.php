<?php

namespace modl;

class Message extends Model {
    public $session;
    public $jidto;
    public $jidfrom;
    
    public $resource;
    
    public $type;

    public $subject;
    public $thread;
    public $body;
    public $html;

    public $published;
    public $delivered;

    public $color; // Only for chatroom purpose

    public function __construct()
    {
        $this->_struct = '
        {
            "session" : 
                {"type":"string", "size":128, "mandatory":true },
            "jidto" : 
                {"type":"string", "size":128, "mandatory":true },
            "jidfrom" : 
                {"type":"string", "size":128, "mandatory":true },
            "resource" : 
                {"type":"string", "size":128 },
            "type" : 
                {"type":"string", "size":20 },
            "subject" : 
                {"type":"text"},
            "thread" : 
                {"type":"string", "size":128 },
            "body" : 
                {"type":"text"},
            "html" : 
                {"type":"text"},
            "published" : 
                {"type":"date"},
            "delivered" : 
                {"type":"date"}
        }';
        
        parent::__construct();
    }

    public function set($stanza, $parent = false)
    {
        if($stanza->body || $stanza->subject) {
            $jid = explode('/',(string)$stanza->attributes()->from);
            $to = current(explode('/',(string)$stanza->attributes()->to));

            // This is not very beautiful
            $user = new \User;
            $this->session    = $user->getLogin();

            $this->jidto      = $to;
            $this->jidfrom    = $jid[0];

            if(isset($jid[1]))
                $this->resource = $jid[1];
            
            $this->type    = (string)$stanza->attributes()->type;
            
            $this->body    = (string)$stanza->body;
            $this->subject = (string)$stanza->subject;

            if($stanza->html) {
                $this->html = \cleanHTMLTags($stanza->html->body->asXML());
                $this->html = \fixSelfClosing($m->html);
            }
            
            if($stanza->delay)
                $this->published = gmdate('Y-m-d H:i:s', strtotime($stanza->delay->attributes()->stamp));
            elseif($parent && $parent->delay)
                $this->published = gmdate('Y-m-d H:i:s', strtotime($parent->delay->attributes()->stamp));
            else
                $this->published = gmdate('Y-m-d H:i:s');
            $this->delivered = gmdate('Y-m-d H:i:s');
        }
    }
}
