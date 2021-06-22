<?php

namespace App\Console\Commands\System;

use Illuminate\Console\Command;

class System extends Command
{
    const COMMANDS = [
        'match:loadOrder',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重新刷新系统的内容';

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

        foreach (self::COMMANDS as $command) {
            $this->call($command);
        }

    }


}
