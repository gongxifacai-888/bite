<?php

namespace App\Console\Commands\Common;

use Illuminate\Console\Command;

class Translate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'common:translate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '翻译';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //需要配置的
        $from = 'zh';
        $to = 'en';
//        $file = 'account_log.php';
//        $file = 'api.php';
        $file = 'model.php';
//        $file = 'notify.php';
//        $file = 'otc.php';
//        $file = 'pagination.php';
//        $file = 'upload.php';
//        $file = 'validation.php';

        $from_path = resource_path("lang/{$from}/{$file}");

        $origin_arr = require $from_path;

        $str = '<?php' . PHP_EOL;

        foreach ($origin_arr as $k => &$lang) {
            try {
                $this->info($k);
                retry(5, function () use ($from, $to, $k, &$lang) {
                    $response = http('http://tool.ysxpark.cn:81/api/translate/index', [
                        'from' => $from,
                        'to' => $to,
                        'words' => $k,
                    ]);
                    $lang = $response['data'];
                }, 1);

            } catch (\Throwable $t) {
                $this->info($t->getMessage());
            }
        }
        $str .= 'return ';
        $str .= var_export($origin_arr, TRUE) . ';';

        $to_path = resource_path("lang/{$to}/{$file}");

        if (!is_dir(resource_path("lang/{$to}"))) {
            mkdir(resource_path("lang/{$to}"));
        }

        file_put_contents($to_path, $str);
    }
}
