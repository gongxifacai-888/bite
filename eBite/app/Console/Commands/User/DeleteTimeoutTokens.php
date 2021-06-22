<?php

namespace App\Console\Commands\User;

use App\Models\User\Token;
use Illuminate\Console\Command;

class DeleteTimeoutTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:deleteTimeoutTokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '删除用户过期token';

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
        Token::where('timeout_at', '<', now())->delete();
    }
}
