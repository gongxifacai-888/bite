<?php

namespace App\Console\Commands\Common;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis as RedisFacade;

class CountRedisQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'count:redis:queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '统计redis队列的任务数量';

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
        $redis = RedisFacade::connection('default');
        $queues = $redis->keys('*queues:*');
        $counts = [];
        foreach ($queues as $key => $value) {
            $value = str_ireplace(config('app.name'). '_', '', $value);
            if ($redis->type($value) == \Redis::REDIS_LIST) {
                $counts[$value] = $redis->llen($value);
            }
        }
        arsort($counts);
        dump($counts);
    }
}
