<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\WebSocketServer\Context;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    /**
     * @param WebSocketServer $server
     */
    public function onMessage( $server, Frame $frame): void
    {
        $username = Context::get('username');
        $server->push($frame->fd, "Recv {$username}: " . $frame->data);
    }
    /**
     * @param Server $server
     */
    public function onClose($server, int $fd, int $reactorId): void
    {
        var_dump('closed');
    }

    /**
     * @param WebSocketServer $server
     */
    public function onOpen($server, Request $request): void
    {
        Context::set('username', $request->cookie['username']);
        $server->push($request->fd, 'Opened');
    }
}
