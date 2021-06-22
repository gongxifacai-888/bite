<?php


namespace App\Http\Controllers\Web;


use App\Models\AppSetting\AppVersion;
use Illuminate\Support\Str;

class DownloadController extends Controller
{
    public function index()
    {
        $android_url = AppVersion::where('type', AppVersion::TYPE_ANDROID)->value('download_url');
        return view('web.download.index', [
            'android' => $android_url,
        ]);
    }

    public function plist()
    {
        $ipa_url = AppVersion::where('type', AppVersion::TYPE_IOS)->value('download_url');

        $xml = file_get_contents(resource_path('views/web/download/plist.xml'));

        $xml = str_replace('$IPA_URL', url($ipa_url), $xml);
        $xml = str_replace('$APP_NAME', config('app.name'), $xml);

        return response($xml)->header('Content-Type', 'text/xml');
    }
}
