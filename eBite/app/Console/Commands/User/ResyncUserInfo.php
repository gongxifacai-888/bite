<?php

namespace App\Console\Commands\User;

use App\Models\User\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResyncUserInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:resyncUserInfo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        foreach (User::cursor() as $user) {

            try {
                /**@var User $user * */

                $user->syncUserInfo();
            } catch (\Throwable $t) {
                $this->error($t->getMessage());
                $this->error($t->getFile());
                $this->error($t->getLine());
            }

        }
    }
}
