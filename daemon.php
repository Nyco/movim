<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Movim\Daemon\Behaviour;

require dirname(__FILE__) . '/vendor/autoload.php';

define('DOCUMENT_ROOT', dirname(__FILE__));
require_once(DOCUMENT_ROOT.'/bootstrap.php');

$bootstrap = new Bootstrap();
$booted = $bootstrap->boot();

$argsize = count($argv);
if($argsize == 1) {
    echo colorize("Please specify a base uri eg.", "red"). colorize(" http://myhost.com/movim/\n", 'yellow');
    exit;
}

if($argsize == 2) {
    echo colorize("Please specify a port eg.", "red"). colorize(" 8080\n", 'yellow');
    exit;
}

$server = new Behaviour($argv[1], $argv[2]);

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            $server
        )
    ),
    $argv[2]
);

$server->run();
