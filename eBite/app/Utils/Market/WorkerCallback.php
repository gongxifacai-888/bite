<?php

namespace App\Utils\Market;

use App\Utils\Market\Huobi\WebSocket\HuobiConnection;

class WorkerCallback
{
    protected $events = [
        'onWorkerStart',
        'onConnect',
        'onMessage',
        'onClose',
        'onError',
        'onBufferFull',
        'onBufferDrain',
        'onWorkerStop',
        'onWorkerReload'
    ];

    protected $wsConnection; //websocket client连接
    protected $worker;

    public function __construct()
    {
        $this->registerEvent();
    }

    public function registerEvent()
    {
        foreach ($this->events as $key => $event) {
            method_exists($this, $event) && $this->$event = [$this, $event];
        }
    }

    public function onWorkerStart($worker)
    {
        $this->worker = $worker;
        echo '进程' . $worker->id .'启动'. PHP_EOL;
        if ($worker->id < 8) {
            $periods = ['1min', '5min', '15min', '30min', '60min', '1day', '1mon', '1week']; //['1day', '1min'];
            $period = $periods[$worker->id];
            $worker->name = 'huobi.ws:' . 'market.kline.' . $period;
        } else {
            $worker->name = 'huobi.ws:' . 'market.depth.step0';
        }
        $ws_con = new HuobiConnection($worker->id);
        $this->wsConnection = $ws_con;
        $ws_con->connect();
    }

    public function onWorkerReload($worker)
    {
    }

    public function onConnect($connection)
    {
    }

    public function onClose($connection)
    {
    }

    public function onError($connection, $code, $msg)
    {
    }

    public function onMessage($connection, $data)
    {
    }
}
