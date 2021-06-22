<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Logic\MicroTrade;

class MicroUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $kline;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($kline)
    {
        $this->kline = $kline;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $match_id = $this->kline->currency_match_id;
//        MicroTrade::newPrice($match_id, $this->kline->close);
        MicroTrade::close($match_id, $this->kline->close);
    }
}
