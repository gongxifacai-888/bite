<?php

/**
 * swl
 *
 * 20180705
 */

namespace App\Console\Commands\Chain;

use App\BlockChain\BlockChain;
use App\Models\Account\AccountDraw;
use App\Models\Currency\Currency;
use App\Models\Chain\TxHash;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateHashStatus extends Command
{
    protected $signature = 'chain:updateHashStatus';

    protected $description = '更新链上哈希状态';

    public function handle()
    {
        try {
            DB::transaction(function () {
                foreach (TxHash::where('status', TxHash::STATUS_WAIT)->cursor() as $txHash) {
                    $this->update($txHash);
                    $this->info("已更新:{$txHash->id}");
                }
            });
        } catch (\Throwable $t) {
            $this->error($t->getMessage());
            $this->error($t->getFile());
            $this->error($t->getLine());
        }
    }

    /**
     * @param TxHash $txHash
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update($txHash)
    {
        try {
            /**@var TxHash $txHash * */
            $successHandle = "{$txHash->callback_handele}SuccessHandle";
            $failedHandle = "{$txHash->callback_handele}FailedHandle";

            $blockChain = BlockChain::newInstance($txHash->currencyProtocol);

            $status = $blockChain->txStatus($txHash->hash);

            if ($status == TxHash::STATUS_SUCCESS && method_exists($this, $successHandle)) {
                $this->$successHandle($txHash);
            }
            if ($status == TxHash::STATUS_FAILED && method_exists($this, $failedHandle)) {
                $this->$failedHandle($txHash);
            }

            $txHash->update([
                'status' => $status
            ]);

        } catch (\Throwable $t) {
            $this->error($t->getMessage());
            $this->error($t->getFile());
            $this->error($t->getLine());
        }
    }

    /**
     * 标记提币成功
     */
    public function drawSuccessHandle($txHash)
    {
        $accountDraw = AccountDraw::where('tx_hash_id', $txHash->id)->first();
        if (!$accountDraw) {
            return;
        }
        $accountDraw->success();
    }

    /**
     * 标记提币失败
     */
    public function drawFailedHandle($txHash)
    {
        $accountDraw = AccountDraw::where('tx_hash_id', $txHash->id)->first();
        if (!$accountDraw) {
            return;
        }
        $accountDraw->failed();

    }


}
