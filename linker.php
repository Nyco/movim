<?php
require __DIR__ . '/vendor/autoload.php';

define('DOCUMENT_ROOT', dirname(__FILE__));
require_once(DOCUMENT_ROOT.'/bootstrap.php');

gc_enable();

$bootstrap = new Bootstrap();
$booted = $bootstrap->boot();

$loop = React\EventLoop\Factory::create();

/*$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);

$connector = new React\SocketClient\Connector($loop, $dns);*/
$connector = new Ratchet\Client\Factory($loop);
$stdin = new React\Stream\Stream(STDIN, $loop);

fwrite(STDERR, colorize(getenv('sid'), 'yellow')." widgets before : ".\sizeToCleanSize(memory_get_usage())."\n");

// We load and register all the widgets
$wrapper = WidgetWrapper::getInstance();
$wrapper->registerAll(true);

fwrite(STDERR, colorize(getenv('sid'), 'yellow')." widgets : ".\sizeToCleanSize(memory_get_usage())."\n");

$conn = null;

$parser = new \Moxl\Parser;

$buffer = '';

$stdin_behaviour = function ($data) use (/*&*/&$conn, $loop, &$buffer, &$connector, &$xmpp_behaviour, &$parser) {
    //if(!isset($buffer)) $buffer = '';    
    if(substr($data, -1) == "") {
        $messages = explode("", $buffer . substr($data, 0, -1));
        $buffer = '';

        //if(isset($conn)) $conn->pause();
    
        foreach ($messages as $message) {
            #fwrite(STDERR, colorize($message, 'yellow')." : ".colorize('received from the browser', 'green')."\n");
            
            $msg = json_decode($message);

            if(isset($msg)) {
                if($msg->func == 'message' && $msg->body != '') {
                    $msg = $msg->body;
                } elseif($msg->func == 'unregister') {
                    $conn->close();
                } elseif($msg->func == 'register') {
                    $cd = new \Modl\ConfigDAO();
                    $config = $cd->get();

                    /*$domain = \Moxl\Utils::getDomain($msg->host);
                    fwrite(STDERR, colorize('open a socket to '.$domain, 'yellow')." : ".colorize('sent to XMPP', 'green')."\n");
                    $connector->create($domain, 5222)->then($xmpp_behaviour);*/
                    $connector($config->websocketurl, array('xmpp'))->then($xmpp_behaviour);
                }
            } else {
                return;
            }
            
            $rpc = new \RPC();
            $rpc->handle_json($msg);

            $msg = json_encode(\RPC::commit());
            \RPC::clear();

            if(!empty($msg)) {
                echo base64_encode(gzcompress($msg, 9))."";
                #fwrite(STDERR, colorize($msg, 'yellow')." : ".colorize('sent to the browser', 'green')."\n");
            }

            $xml = \Moxl\API::commit();
            \Moxl\API::clear();
                
            if(!empty($xml)) {
                //$conn->write(trim($xml));
                $conn->send(trim($xml));
                #fwrite(STDERR, colorize(trim($xml), 'yellow')." : ".colorize('sent to XMPP', 'green')."\n");
            }
        }

        //if(isset($conn)) $conn->resume();
    } else {
        $buffer .= $data;
    }
};

//$xmpp_behaviour = function (React\Stream\Stream $stream) use (&$conn, $loop, &$stdin, $stdin_behaviour, $parser) {
$xmpp_behaviour = function (Ratchet\Client\WebSocket $stream) use (&$conn, $loop, &$stdin, $stdin_behaviour, $parser) {
    $conn = $stream;
    fwrite(STDERR, colorize(getenv('sid'), 'yellow')." : ".colorize('linker launched', 'blue')."\n");
    fwrite(STDERR, colorize(getenv('sid'), 'yellow')." launched : ".\sizeToCleanSize(memory_get_usage())."\n");

    $stdin->removeAllListeners('data');
    $stdin->on('data', $stdin_behaviour);

    $conn->bufferSize = 4096*4;
    $conn->on('message', function($message) use (&$conn, $loop, $parser/*, $stream*/) {

        //$conn->pause();

        if(!empty($message)) {
            $restart = false;
       
            if($message == '</stream:stream>') {
                $conn->close();
                $loop->stop();
            } elseif($message == "<proceed xmlns='urn:ietf:params:xml:ns:xmpp-tls'/>") {
                stream_set_blocking($conn->stream, 1);
                $out = stream_socket_enable_crypto($conn->stream, 1, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                stream_set_blocking($conn->stream, 0);
                $restart = true;
            }

            #fwrite(STDERR, colorize($message, 'yellow')." : ".colorize('received', 'green')."\n");

            \Moxl\API::clear();
            \RPC::clear();

            if(!$parser->parse($message)) {
                fwrite(STDERR, colorize(getenv('sid'), 'yellow')." ".$parser->getError()."\n");
            }

            //\Moxl\Xec\Handler::handleStanza($message);

            if($restart) {
                $session = \Sessionx::start();
                \Moxl\Stanza\Stream::init($session->domain);
                $restart = false;
            }

            $xml = \Moxl\API::commit();
            \Moxl\API::clear();

            if(!empty($xml)) {
                //$conn->write(trim($xml));
                $conn->send(trim($xml));
                #fwrite(STDERR, colorize(trim($xml), 'yellow')." : ".colorize('sent to XMPP', 'green')."\n");
            }

            $msg = \RPC::commit();
            \RPC::clear();

            if(!empty($msg)) {
                echo base64_encode(gzcompress(json_encode($msg), 9))."";
                #fwrite(STDERR, colorize($msg.' '.strlen($msg), 'yellow')." : ".colorize('sent to browser', 'green')."\n");
            }

            //$loop->tick();
        }

        //$conn->resume();
    });

    $conn->on('error', function($msg) use ($conn, $loop) {
        #fwrite(STDERR, colorize(serialize($msg), 'red')." : ".colorize('error', 'green')."\n");
        $loop->stop();
    });

    $conn->on('close', function($msg) use ($conn, $loop) {
        #fwrite(STDERR, colorize(serialize($msg), 'red')." : ".colorize('closed', 'green')."\n");
        $loop->stop();
    });

    // And we say that we are ready !
    $obj = new \StdClass;
    $obj->func = 'registered';

    echo base64_encode(gzcompress(json_encode($obj), 9))."";
};

$stdin->on('data', $stdin_behaviour);
$stdin->on('error', function() use($loop) { $loop->stop(); } );
$stdin->on('close', function() use($loop) { $loop->stop(); } );

$loop->run();
