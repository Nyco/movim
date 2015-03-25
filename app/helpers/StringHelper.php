<?php

use HeyUpdate\Emoji\Emoji;
use HeyUpdate\Emoji\EmojiIndex;

/**
 * @desc A singleton wrapper for the Emoji library
 */
class MovimEmoji
{
    protected static $instance = null;
    private $_emoji;
    private $_theme;

    protected function __construct()
    {
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();
        $this->_theme = $config->theme;

        $this->_emoji = new Emoji(new EmojiIndex(), $this->getPath());
    }

    public function replace($string, $large = false)
    {
        $this->_emoji->setAssetUrlFormat($this->getPath($large));
        $string = $this->_emoji->replaceEmojiWithImages($string);
        $this->_emoji->setAssetUrlFormat($this->getPath());
        
        return $string;
    }

    private function getPath($large = false)
    {
        $path = BASE_URI . 'themes/' . $this->_theme . '/img/emojis/';
        if($large) $path .= 'large/';

        return $path.'%s.png';
    }

    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new MovimEmoji;
        }
        return static::$instance;
    }
}

/**
 * @desc Prepare the string (add the a to the links and show the smileys)
 *
 * @param string $string
 * @return string
 */
function prepareString($string, $large = false) {
    //replace begin by www
    $string = preg_replace_callback(
            '/(^|\s|>)(www.[^<> \n\r]+)/ix', function ($match) {
                //print '<br />preg[1]';\system\Debug::dump($match);
                if (strlen($match[2])>0) {
                    return stripslashes($match[1].'<a href=\"http://'.$match[2].'\" target=\"_blank\">'.$match[2].'</a>');
                } else {
                    return $match[2];
                }
            }, ' ' . $string
    );

    //replace  begin by http - https (before www)
    $string = preg_replace_callback(
            '/(?(?=<a[^>]*>.+<\/a>)(?:<a[^>]*>.+<\/a>)|([^="\'])((?:https?):\/\/([^<> \n\r]+)))/ix', function ($match) {
                if (isset($match[2]) && strlen($match[2])>0) {
                    return stripslashes($match[1].'<a href=\"'.$match[2].'\" target=\"_blank\">'.$match[3].'</a>');
                } else {
                    return $match[0];
                }
            }, ' ' . $string
    );
    
    // We remove all the style attributes
    $string = preg_replace_callback(
        '/(<[^>]+) style=".*?"/i', function($match) {
            return $match[1];
        }, $string    
    );
    
    // Twitter hashtags
    $string = preg_replace_callback(
        "/ #[a-zA-Z0-9_-]{3,}/", function ($match) {
            return
                ' <a class="twitter hastag" href="http://twitter.com/search?q='.
                    urlencode(trim($match[0])).
                    '&src=hash" target="_blank">'.
                    trim($match[0]).
                '</a>';
        }, ' ' . $string
    );

    $string = preg_replace_callback(
        "/ @[a-zA-Z0-9_-]{3,}/", function ($match) {
            return
                ' <a class="twitter at" href="http://twitter.com/'.
                    trim($match[0]).
                    '" target="_blank">'.
                    trim($match[0]).
                '</a>';
      }, ' ' . $string
    );

    //remove all scripts
    $string = preg_replace_callback(
            '#<[/]?script[^>]*>#is', function ($match) {
                return '';
            }, ' ' . $string
    );
    //remove all iframe
    $string = preg_replace_callback(
            '#<[/]?iframe[^>]*>#is', function ($match) {
                return '';
            }, ' ' . $string
    );
    //remove all iframe
    $string = preg_replace_callback(
            '#<[/]?ss[^>]*>#is', function ($match) {
                return '';
            }, ' ' . $string
    );
   
    // We add some smileys...
    $emoji = MovimEmoji::getInstance();
    $string = $emoji->replace($string, $large);
    
    return trim($string);
}


/**
 * Fix self-closing tags
 */
function fixSelfClosing($string) {
    return preg_replace_callback('/<([^\s<]+)\/>/',
        function($match) {
            return '<'.$match[1].'></'.$match[1].'>';
        }
        , $string);
}

/**
 * Remove the content, body and html tags
 */
function cleanHTMLTags($string) {
    return str_replace(
        array(
            '<content type="html">',
            '<html xmlns="http://jabber.org/protocol/xhtml-im">',
            '<body xmlns="http://www.w3.org/1999/xhtml">',
            '</body>',
            '</html>', 
            '</content>'),
        '',
        $string);
}

/**
 * Return an array of informations from a XMPP uri
 */
function explodeURI($uri) {
    $arr = parse_url(urldecode($uri));
    $result = array();
    
    if(isset($arr['query'])) {
        $query = explode(';', $arr['query']);


        foreach($query as $elt) {
            if($elt != '') {
                list($key, $val) = explode('=', $elt);
                $result[$key] = $val;
            }
        }

        $arr = array_merge($arr, $result);
    }
    
    return $arr;

}

/*
 * Echap the JID 
 */
function echapJid($jid)
{
    return str_replace(' ', '\40', $jid);
}

/*
 * Echap the anti-slashs for Javascript 
 */
function echapJS($string)
{
    return str_replace("\\", "\\\\", $string);
}

/*
 * Clean the resource of a jid
 */
function cleanJid($jid)
{
    $explode = explode('/', $jid);
    return reset($explode);
}

/*
 *  Explode JID
 */
function explodeJid($jid)
{
    list($jid, $resource) = explode('/', $jid);
    list($username, $server) = explode('@', $jid);

    return array(
        'username'  => $username,
        'server'    => $server,
        'resource' => $resource
        );
}

/**
 * Return a URIfied string
 * @param string
 * @return string
 */
function stringToUri($url) {
    $url = utf8_decode($url);
    $url = strtolower(strtr($url, utf8_decode('ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ()[]\'"~$&%*@ç!?;,:/\^¨€{}<>|+- '),  'aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn    --      c  ---    e      --'));
    $url = str_replace(' ', '', $url);
    $url = str_replace('---', '-', $url);
    $url = str_replace('--', '-', $url);
    $url = trim($url,'-');
    return $url;
}

/**
 * Return a human readable filesize
 * @param string size in bytes
 * @return string
 */
function sizeToCleanSize($size)
{
    $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $power = $size > 0 ? floor(log($size, 1024)) : 0;
    return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}

/**
 * Return a colored string in the console
 * @param string
 * @param color
 * @return string
 */
function colorize($string, $color) {
    $colors = array(
        'black'     => 30,
        'red'       => 31,
        'green'     => 32,
        'yellow'    => 33,
        'blue'      => 34,
        'purple'    => 35,
        'turquoise' => 36,
        'white'     => 37
    );

    return "\033[".$colors[$color]."m".$string."\033[0m";
}


/**
 * Return a color generated from the string
 * @param string
 * @return string
 */
function stringToColor($string) {
    $colors = array(
        0 => 'red',
        1 => 'purple',
        2 => 'indigo',
        3 => 'blue',
        4 => 'green',
        5 => 'orange',
        6 => 'yellow',
        7 => 'brown');
        
    $s = substr(base_convert(sha1($string), 15, 10), 0, 10);
    
    if($colors[$s%8]) {
        return $colors[$s%8];
    } else {
        return 'orange';
    }
}

/**
 * Return the first letter of a string
 * @param string
 * @return string
 */
function firstLetterCapitalize($string) {
    return ucfirst(strtolower(mb_substr($string, 0, 2)));
}
