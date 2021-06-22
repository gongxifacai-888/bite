<?php
/**
 * Created by PhpStorm.
 * User: YSX
 * Date: 2019/3/21
 * Time: 16:45
 */

namespace App\Http\Controllers\Admin;

use App\Uploader\Local;
use App\Uploader\Uploader;

class UploadController extends Controller
{
    public function layuiUpload()
    {
        $file = request()->file('file');

        $msg = Uploader::newInstance(Local::class)->upload($file);

        return response()->json([
            'code' => 0,
            'msg' => '上传成功',
            'data' => [
                'src' => $msg['path']
            ],
        ]);
    }
}
