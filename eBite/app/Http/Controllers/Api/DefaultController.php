<?php


namespace App\Http\Controllers\Api;

use App\Models\AppSetting\AppVersion;
use App\Models\System\Lang;
use App\Uploader\Local;
use App\Uploader\Uploader;
use Illuminate\Http\Request;
use App\Models\System\Area;
use App\Models\Setting\Setting;
use Artisan;

class DefaultController extends Controller
{
    public function test()
    {
        $level = request('level', -1);
        $str   = gzencode('123456', $level);
        // $str = gzcompress('123456');
        // header("Content-Type: text/plain");
        ob_start();
        Artisan::call('common:test');
        ob_end_clean();
        return $this->success('ok');
        // echo base64_encode($str);
    }

    // 获取国家
    public function areaList(Request $request)
    {
        $list = Area::orderBy('sort', 'asc')->orderBy('id', 'asc')->get();
        return $this->success("", $list);
    }

    // 获取语言
    public function langList()
    {
        $list = Lang::where('status', 1)
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')->get();
        return $this->success("", $list);
    }

    // 上传图片
    public function imageUpload(Request $request)
    {
        try {
            $file = request()->file('file');

            $msg = Uploader::newInstance(Local::class)->upload($file);
            return $this->success(__('upload.Upload Success'), $msg);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    // 获取设置
    public function setting()
    {
        $key   = request('key', '');
        $value = Setting::getValueByKey($key, '', true);
        return $this->success(__('api.请求成功'), $value);
    }

    public function checkUpdate()
    {
        $version_number = request('version_number');
        $type           = request('type');
        $os             = $type == 1 ? 'Android' : 'Ios';
        if (is_null($type) || is_null($version_number)) {
            return $this->error(__('api.参数异常'));
        }
        $new_version = AppVersion::where('type', $type)
            ->orderBy('created_at', 'DESC')
            ->first();
        if (is_null($new_version)) {
            return $this->error(__('api.版本异常'));
        }
        $whether = version_compare($version_number, $new_version->version_number, '>=');
        if ($whether) {
            return $this->error(__('api.您的APP不需要更新'));
        }
        $main_version     = substr($version_number, 0, strpos($version_number, '.'));
        $new_main_version = substr($new_version->version_number, 0, strpos($new_version->version_number, '.'));
        $wgt_url          = $new_version->wgt_url;
        $pkg_url          = $new_version->pkg_url;
        if ($new_main_version > $main_version) {
            $wgt_url = '';
        } else {
            $pkg_url = '';
        }

        if($type==2){
            $u=config('app.url').'/download';


        }else{
                        // $u=Setting::getValueByKey('ios','');

            $u=config('app.url') . $new_version->download_url;
        }
        return $this->success("$os" . __('api.发现新版本'), [
            'pkg_url'      => $pkg_url,
            'wgt_url'      => $wgt_url,
            'download_url' => $u,
            'update'       => true,
        ]);
    }
}
