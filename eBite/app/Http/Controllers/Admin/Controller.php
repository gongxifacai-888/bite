<?php


namespace App\Http\Controllers\Admin;


class Controller extends \App\Http\Controllers\Controller
{
    
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

}
