<?php

namespace App\Console\Commands\Common;

use Illuminate\Console\Command;

class Lbxenc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lbxenc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'lbx encrypt';

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
        $except_files = [
            'Console/Kernel.php',
            'Http/Kernel.php',
        ];
        if (!extension_loaded('lbxenc')) {
            throw new \Exception('请先安装lbxenc扩展');
        }
        array_walk($except_files, function (&$item, $key) {
            $item = realpath(app_path($item));
        });

        $this->encDir(app_path(''), $except_files);
    }

    public function encDir($path, $except_files)
    {
        $DirectoriesIt = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
        $AllIt = new \RecursiveIteratorIterator($DirectoriesIt);
        $it = new \RegexIterator($AllIt, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
        foreach ($it as $v) {
            if (!in_array(realpath($v[0]), $except_files)) {
                $this->encrypt($v[0]);
            }
        }
    }

    function encrypt($in, $out = null)
    {
        is_null($out) && $out = $in;
        $fp = fopen($in, 'rb');
        $fileSize = filesize($in);
        if (is_resource($fp) && $fileSize) {
            $data = lbxenc_encode(fread($fp, $fileSize));
            if ($data !== false) {
                if (file_put_contents($out, '') !== false) {
                    $out_fp = fopen($out, 'rb+');
                    rewind($out_fp);
                    fwrite($out_fp, $data);
                    fclose($out_fp);
                }
            }
            fclose($fp);
        }
    }
}
