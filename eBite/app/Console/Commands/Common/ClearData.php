<?php

namespace App\Console\Commands\Common;

use App\Entity\TxCompleteEntity;
use App\Models\Account\AccountDraw;
use App\Models\Account\ChangeAccount;
use App\Models\Account\ChangeAccountLog;
use App\Models\Account\LegalAccount;
use App\Models\Account\LegalAccountLog;
use App\Models\Account\LeverAccount;
use App\Models\Account\LeverAccountLog;
use App\Models\Account\MicroAccount;
use App\Models\Account\MicroAccountLog;
use App\Models\Account\TransferredLog;
use App\Models\Admin\Admin;
use App\Models\Admin\AdminRole;
use App\Models\Admin\OperationLog;
use App\Models\Agent\Agent;
use App\Models\Agent\AgentMoneyLog;
use App\Models\Chain\ChainWallet;
use App\Models\Chain\TxHash;
use App\Models\Feedback\Feedback;
use App\Models\Lever\LeverTransaction;
use App\Models\Micro\MicroOrder;
use App\Models\Otc\OtcAction;
use App\Models\Otc\OtcDetail;
use App\Models\Otc\OtcMaster;
use App\Models\Otc\OtcMasterLog;
use App\Models\Otc\OtcPayway;
use App\Models\Otc\Seller;
use App\Models\Otc\SellerApply;
use App\Models\Otc\SellerCurrency;
use App\Models\Otc\SellerCurrencyApply;
use App\Models\System\Area;
use App\Models\Tx\TxComplete;
use App\Models\Tx\TxHistory;
use App\Models\Tx\TxIn;
use App\Models\Tx\TxOut;
use App\Models\User\NotifyLog;
use App\Models\User\Token;
use App\Models\User\User;
use App\Models\User\UserPayway;
use App\Models\User\UserReal;
use App\Notify\Notify;
use App\Notify\SMS\Template\SMSLoginCode;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**清数据用的
 * Class ClearData
 *
 * @package App\Console\Commands\Common
 */
class ClearData extends Command
{

    protected $signature = 'common:clearData';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {

            AccountDraw::truncate();
            ChangeAccountLog::truncate();
            LegalAccountLog::truncate();
            LeverAccountLog::truncate();
            MicroAccountLog::truncate();
            TransferredLog::truncate();
            OperationLog::truncate();
            AgentMoneyLog::truncate();
            LegalAccount::truncate();
            TxHash::truncate();
            Feedback::truncate();
            LeverTransaction::truncate();
            MicroOrder::truncate();
            OtcAction::truncate();
            OtcDetail::truncate();
            OtcMaster::truncate();
            OtcMasterLog::truncate();
            OtcPayway::truncate();
            Seller::truncate();
            SellerApply::truncate();
            SellerCurrency::truncate();
            SellerCurrencyApply::truncate();
            TxComplete::truncate();
            TxHistory::truncate();
            TxIn::truncate();
            TxOut::truncate();
            NotifyLog::truncate();
            Token::truncate();
            UserPayway::truncate();
            UserReal::truncate();

            Admin::where('username', '<>', 'admin')->delete();
            Agent::where('username', '<>', 'admin')->delete();
            User::where('mobile', '<>', '18888888888')->delete();

            $user_id = User::value('id');
            ChangeAccount::where('user_id', '<>', $user_id)->delete();
            LegalAccount::where('user_id', '<>', $user_id)->delete();
            LeverAccount::where('user_id', '<>', $user_id)->delete();
            MicroAccount::where('user_id', '<>', $user_id)->delete();
            ChainWallet::where('user_id', '<>', $user_id)->delete();

        } catch (\Throwable $t) {
            $this->error($t->getMessage());
            $this->error($t->getFile());
            $this->error($t->getLine());
        }
    }

}
