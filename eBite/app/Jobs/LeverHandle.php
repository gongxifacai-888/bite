<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Lever\LeverTransaction;

class LeverHandle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        extract($this->params);
        //价格大于0才做更新处理
        if (bc($now_price,'>', '0') > 0) {
            LeverTransaction::tradeHandle($currency_match, $now_price, $now);
        } else {
            echo '计价币id:' . $this->quote_currency_id . ',交易币id:' .$this->base_currency_id . '当前行情价格异常' . PHP_EOL;
        }
    }
}
