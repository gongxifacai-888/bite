<?php

namespace App\Console;

use App\Console\Commands\Socket\GatewayService;
use App\Console\Commands\Socket\Socket;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
          GatewayService::class,
           Socket::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //定时更新交易哈希
        $schedule->command('chain:updateHashStatus')
            ->runInBackground()
            ->everyTenMinutes();
        //每日凌晨清空成交量和成交额
        $schedule->command('market:clearQuotation')
            ->runInBackground()
            ->daily();
        //更新币种表的实时价格,此接口有频率限制,尽量不要请求太快
        $schedule->command('market:updateCurrencyPrice')
            ->runInBackground()
	    // ->hourly();
	    ->everyMinute();
        //定时删除用户超时token
        $schedule->command('user:deleteTimeoutTokens')
            ->runInBackground()
            ->everyMinute();
        //充币
        $schedule->command('account:billRecharge')
            ->runInBackground()
            ->withoutOverlapping()
            ->everyMinute()
            ->appendOutputTo(storage_path("logs/bill_recharge/") . date('Y-m-d') . ".log");
        // 法币自动改变超时交易状态
        $schedule->command('otc:auto:job')
            ->runInBackground()
            ->everyMinute()
            ->appendOutputTo(storage_path("logs/otc/") . date('Y-m-d') . ".log");
        //释放理财账户收益
        $schedule->command('patch:releaseStorageCurrency')
            ->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
