<?php


namespace App\Http\Controllers\Web;

class Controller extends \App\Http\Controllers\Controller
{

    public function success($msg, $data = [])
    {
        if (!$msg) {
            $msg = '请求成功';
        }
        return json_response_success($msg, $data);
    }

    public function error($msg, $data = [])
    {
        return json_response_error($msg, $data);
    }

}
