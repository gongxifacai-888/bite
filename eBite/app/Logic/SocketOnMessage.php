<?php

namespace App\Logic;

use Illuminate\Support\Carbon;
use GatewayWorker\Lib\Gateway;
use App\Models\User\Token;

class SocketOnMessage
{
    public static function messageHandler($event)
    {
        $message = $event->message;
        $client_id = $event->clientId;
        if (is_json($message)) {
            $message = json_decode($message, true);
            if (isset($message['type'])) {
                $event = ucfirst($message['type']);
                $method = "do{$event}";
                method_exists(self::class, $method) && call_user_func_array([self::class, $method], [$client_id, $message]);
            }
        } else {
            echo date('Y-m-d H:i:s') . ' 接收到未知的消息体:' . PHP_EOL;
            dump($message);
        }
    }

    /**
     * 用户登录
     *
     * @param string $token
     * @param string $client_id
     *
     * @return boolean
     */
    public static function doLogin($client_id, $message)
    {
        $now = Carbon::now();
        $token = $message['params'];
        $user_id = Token::getUserIdByToken($token);
        if ($user_id == 0) {
            $send_data = [
                'type' => 'login_result',
                'code' => -1,
                'msg' => '登录失败',
            ];
            SocketPusher::sendMsg('login_result', $send_data, SocketPusher::CLIENT, $client_id);
            return false;
        }
        Gateway::bindUid($client_id, $user_id);
        $send_data = [
            'type' => 'login_result',
            'code' => 1,
            'msg' => '登录成功',
        ];
        SocketPusher::sendMsg('login_result', $send_data, SocketPusher::CLIENT, $client_id);
        $send_data_str = json_encode($send_data, JSON_UNESCAPED_UNICODE);
        echo $send_data_str . PHP_EOL;
        return true;
    }

    /**
     * 用户登出
     *
     * @param string $token
     * @param string $client_id
     *
     * @return boolean
     */
    public static function doLogout($client_id, $message)
    {
        $now = Carbon::now();
        $token = $message['params'];
        $user_id = Token::getUserIdByToken($token);
        if ($user_id == 0) {
            $send_data = [
                'type' => 'login_result',
                'code' => -1,
                'msg' => '登出失败',
                // 'extra' => gzencode("123456"),
            ];
            SocketPusher::sendMsg('login_result', $send_data, SocketPusher::CLIENT, $client_id);
            return false;
        }
        Gateway::unbindUid($client_id, $user_id);
        $send_data = [
            'type' => 'login_result',
            'code' => 1,
            'msg' => '登出成功',
            // 'extra' => gzencode("123456"),
        ];
        SocketPusher::sendMsg('login_result', $send_data, SocketPusher::CLIENT, $client_id);
        return true;
    }

    public static function doSub($client_id, $message)
    {
        $params = strtoupper($message['params']);
        Gateway::joinGroup($client_id, $params);
        SocketPusher::sendMsg('sub_result', [
            'msg' => '订阅成功' . $params,
        ], SocketPusher::CLIENT, $client_id);
    }

    public static function doUnsub($client_id, $message)
    {
        $params = strtoupper($message['params']);
        Gateway::leaveGroup($client_id, $params);
        SocketPusher::sendMsg('unsub_result', [
            'msg' => '取消订阅成功' . $params
        ], SocketPusher::CLIENT, $client_id);
    }

}
