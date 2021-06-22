<?php

namespace App\Jobs;

use App\Logic\Market;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;

class UpdateSocketKline implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $symbol;
    protected $period;
    protected $id;
    protected $kline_data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($symbol, $period, $id, $kline_data)
    {
        $this->symbol = $symbol;
        $this->period = $period;
        $this->id = $id;
        $this->kline_data = $kline_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $control_market_key = "control:{$this->symbol}:{$this->period}:{$this->id}";

        $control_data = Cache::get($control_market_key, []);

        if ($control_data) {

            /**TODO 改变kline_data**/
        }


        $kline_data = Market::updateSocketKline($this->symbol, $this->period, $this->kline_data);
    }
}
