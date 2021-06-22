<?php

namespace App\Console\Commands\Common;

use App\Models\Currency\CurrencyMatch;
use App\Models\Lever\LeverTransaction;
use App\Utils\Workerman\WsConnection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Jobs\{LeverClosing, LeverPushTrade, LeverHandle, SendMarket};


class Ceshi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'common:ceshi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ceshi';

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

    }


}
