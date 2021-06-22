<?php

namespace App\Console\Commands\Robot;

use App\Console\Commands\Socket\InitArgument;
use App\Logic\Robot;
use App\Models\Currency\CurrencyQuotation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Workerman\Lib\Timer;
use App\Models\Tx\Robot as RobotModel;

class Worker extends Command
{
    use InitArgument;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'robot:worker {worker_command} {--mode=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '机器人主进程';

    /**
     * @var \Workerman\Worker
     */
    protected $worker;

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
        $this->initArgv();
        $this->resetRobotPrice();

        $this->worker = new \Workerman\Worker();
        $this->worker->count = 10;//默认10个机器人进程
        $this->worker->name = 'robot:worker';//默认10个机器人进程

        $this->worker->onWorkerStart = [$this, 'onWorkerStart'];

        //设置pid路径
        $path = storage_path('robot/');
        file_exists($path) || @mkdir($path);
        \Workerman\Worker::$pidFile = $path . 'worker.pid';
        \Workerman\Worker::runAll();
    }

    public function onWorkerStart()
    {
        try {
            $robot = new Robot($this->worker->id);
            Timer::add(1, function () use ($robot) {
                $robot->run();
            });
        } catch (\Throwable $t) {
            $this->info($t->getMessage());
            $this->info($t->getFile());
            $this->info($t->getLine());
        }
    }

    /**开启的时候重置一下机器人的价格,否则价格可能会是很长时间之前的价格,导致很大的涨跌幅
     *
     */
    public function resetRobotPrice()
    {
        foreach (RobotModel::cursor() as $robot) {
            /**@var RobotModel $robot * */
            $price = CurrencyQuotation::where('currency_match_id', $robot->currency_match_id)
                ->value('close');
            $robot->update([
                'price' => $price
            ]);
        }
    }

}
