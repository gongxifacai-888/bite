<?php

namespace App\Console\Commands\Otc;

use Illuminate\Console\Command;
use App\Models\Otc\OtcDetail;
use App\Models\Otc\OtcMaster;

class AutoJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otc:auto:job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '法币交易自动化脚本';

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
        OtcDetail::getUnpayJobs()->each(function ($item, $key) {
            try {
                OtcDetail::cancel($item, null, OtcMaster::OPERATOR_SYSTEM);
            } catch (\Throwable $th) {
                $this->error($th->getMessage());
            }
        });
        OtcDetail::getUnConfrimJobs()->each(function ($item, $key) {
            try {
                OtcDetail::confirm($item, null, OtcMaster::OPERATOR_SYSTEM);
            } catch (\Throwable $th) {
                $this->error($th->getMessage());
            }
        });
        OtcDetail::getDeferedUnConfrimJobs()->each(function ($item, $key) {
            try {
                OtcDetail::confirm($item, null, OtcMaster::OPERATOR_SYSTEM);
            } catch (\Throwable $th) {
                $this->error($th->getMessage());
            }
        });
    }
}
