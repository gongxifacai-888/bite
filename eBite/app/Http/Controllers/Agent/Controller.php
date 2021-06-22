<?php


namespace App\Http\Controllers\Agent;

class Controller extends \App\Http\Controllers\Controller
{
    public function success($msg, $data = [])
    {
        return response()->json([
            'code' => 1,
            'msg' => $msg,
            'data' => $data,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
   
    public function error($msg, $data = [])
    {
        return response()->json([
            'code' => 0,
            'msg' => $msg,
            'data' => $data,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function layuiPageData($paginate, $msg = '', $extra_data = [])
    {
        return response()->json([
            'code' => 0,
            'msg' => $msg,
            'count' => $paginate->total(),
            'data' => $paginate->items(),
            'extra_data' => $extra_data,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function ajaxReturn( $data = [] , $msg = ''  , $code = 1){
        return response()->json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}