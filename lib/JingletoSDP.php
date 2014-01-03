<?php 

class JingletoSDP {
    private $sdp = '';
    private $jingle;
    
    private $values = array(
        'session_id'        => 1,
        'session_version'   => 0,
        'nettype'           => 'IN',
        'addrtype'          => 'IP4',
        'unicast_address'   => '0.0.0.0'
        );
    
    function __construct($jingle) {
        $this->jingle = $jingle;
    }

    function generate() {
        $username = current(explode('@', $this->jingle->attributes()->initiator));
        $sessid   = $this->jingle->attributes()->sid;
        $this->values['session_id']   = substr(base_convert($sessid, 30, 10), 0, 6);
        
        $sdp_version =
            'v=0';
            
        $sdp_origin = 
            'o='.
            $username.' '.
            $this->values['session_id'].' '.
            $this->values['session_version'].' '.
            $this->values['nettype'].' '.
            $this->values['addrtype'].' '.
            $this->values['unicast_address'];
            
        $sdp_session_name =
            's=SIP Call'; // Use the sessid ?
            
        $sdp_timing =
            't=0 0';
            
        $sdp_media = '';
            
        foreach($this->jingle->children() as $content) {
            $sdp_media .= 
                "\nm=".$content->description->attributes()->media.
                "\nc=IN IP4 0.0.0.0".
                "\na=rtcp:1 IN IP4 0.0.0.0";
                
            if(isset($content->transport->attributes()->ufrag))
                $sdp_media .= "\na=ice-ufrag:".$content->transport->attributes()->ufrag;
                
            if(isset($content->transport->attributes()->pwd))
                $sdp_media .= "\na=ice-pwd:".$content->transport->attributes()->pwd;
            
            foreach($content->description->children() as $payload) {
                switch($payload->getName()) {
                    case 'rtp-hdrext':  
                        $sdp_media .= 
                            "\na=extmap:".
                            $payload->attributes()->id;      
                            
                        if(isset($payload->attributes()->senders))
                            $sdp_media .= ' '.$payload->attributes()->senders;

                        $sdp_media .= ' '.$payload->attributes()->uri;
                        break;
                        
                    case 'rtcp-mux':
                        $sdp_media .= 
                            "\na=rtcp-mux"; 
                    
                    case 'encryption':
                        if(isset($payload->crypto)) {
                            $sdp_media .= 
                                "\na=crypto:".
                                $payload->crypto->attributes()->tag.' '.                          
                                $payload->crypto->attributes()->{'crypto-suite'}.' '.                          
                                $payload->crypto->attributes()->{'key-params'};

                            // TODO session params ?
                        }
                        break;

                    case 'payload-type':
                        $sdp_media .= 
                            "\na=rtpmap:".
                            $payload->attributes()->id;

                        if(isset($payload->attributes()->name)) {
                            $sdp_media .= ' '.$payload->attributes()->name;

                            if(isset($payload->attributes()->clockrate)) {
                                $sdp_media .= '/'.$payload->attributes()->clockrate;

                                if(isset($payload->attributes()->channels)) {
                                    $sdp_media .= '/'.$payload->attributes()->channels;
                                }
                            }
                        }

                        foreach($payload->children() as $rtcpfb) {
                            if($rtcpfb->getName() == 'rtcp-fb') {
                                $sdp_media .= 
                                    "\na=rtcp-fb:".
                                    $rtcpfb->attributes()->id.' '.
                                    $rtcpfb->attributes()->type;

                                if(isset($rtcpfb->attributes()->subtype)) {
                                    $sdp_media .= ' '.$rtcpfb->attributes()->subtype;
                                }
                            }

                            // TODO rtcp_fb_trr_int ?
                        }
                        break;

                    case 'fmtp':
                        // TODO
                        break;

                    case 'source':
                        foreach($payload->children() as $s) {
                            $sdp_media .= 
                                "\na=ssrc:".$payload->attributes()->id.' '.
                                $s->attributes()->name.':'.
                                $s->attributes()->value;
                        }
                        break;
                }
                
                // TODO sendrecv ?
            }

            if(isset($content->description->attributes()->ptime)) {
                $sdp_media .= 
                    "\na=ptime:".$content->description->attributes()->ptime;
            }
            
            if(isset($content->description->attributes()->maxptime)) {
                $sdp_media .= 
                    "\na=maxptime:".$content->description->attributes()->maxptime;
            }

            foreach($content->transport->children() as $payload) {
                switch($payload->getName()) {
                    case 'fingerprint':
                        if(isset($content->transport->fingerprint->attributes()->hash)) {
                            $sdp_media .= 
                                "\na=fingerprint:".
                                $content->transport->fingerprint->attributes()->hash.
                                ' '.
                                $content->transport->fingerprint;    
                        }
                        
                        if(isset($content->transport->fingerprint->attributes()->setup)) {
                            $sdp_media .= 
                                "\na=setup:".
                                $content->transport->fingerprint->attributes()->setup;                    
                        }
                        break;

                    case 'candidate':
                        $sdp_media .= 
                            "\na=candidate:".
                            $payload->attributes()->foundation.' '.
                            $payload->attributes()->component.' '.
                            $payload->attributes()->protocol.' '.
                            $payload->attributes()->priority.' '.
                            $payload->attributes()->ip.' '.
                            $payload->attributes()->port.' '.
                            'typ '.$payload->attributes()->type;

                        if(isset($payload->attributes()->{'rel-addr'})
                        && isset($payload->attributes()->{'rel-port'})) {
                            $sdp_media .=
                                ' raddr '.$payload->attributes()->{'rel-addr'}.
                                ' rport '.$payload->attributes()->{'rel-port'};
                        }

                        if(isset($payload->attributes()->generation)) {
                            $sdp_media .=
                                ' generation '.$payload->attributes()->generation;
                        }
                        break;
                }
            }
        }
        
        $this->sdp .= $sdp_version;
        $this->sdp .= "\n".$sdp_origin;
        $this->sdp .= "\n".$sdp_session_name;
        $this->sdp .= "\n".$sdp_timing;
        $this->sdp .= $sdp_media;
        
        return $this->sdp;
    }
}
