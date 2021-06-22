<?php

namespace App\Console\Commands\Chain;

use App\BlockChain\BlockChain;
use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**给所有用户重新生成链上钱包
 * Class RegenerateChainWallet
 *
 * @package App\Console\Commands\Chain
 */
class RegenerateChainWallet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chain:regenerateChainWallet';

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
                DB::transaction(function () use ($user) {
                    $this->info('开始生成:' . $user->id);
                    BlockChain::generateAllOnlineWallet($user);
                });
            } catch (\Throwable $t) {
                $this->error($t->getMessage());
                $this->error($t->getFile());
                $this->error($t->getLine());
            }
        }
    }
}
