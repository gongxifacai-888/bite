<?php

namespace App\Console\Commands\Common;

use App\Models\User\User;
use App\Notifications\VerificationCode\BaseCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;


class Test extends Command
{

    protected $signature = 'test';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            DB::transaction(function () {
                $user = User::where('email', 'yixiaoyaotian@163.com')->first();
                Notification::send($user, BaseCode::getCodeNotifyByType(1));
            });
        } catch (\Throwable $t) {
            $this->info($t->getMessage());
            $this->info($t->getFile());
            $this->info($t->getLine());
        }
    }
}
