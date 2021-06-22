<?php


namespace App\Http\Controllers\Admin;

use App\BlockChain\BlockChain;
use App\Http\Requests\Admin\Currency\CurrencySaveRequest;
use App\Http\Requests\Admin\Currency\CurrencyUpdateRequest;
use App\Models\Account\AccountType;
use App\Models\Currency\Currency;
use App\Models\Setting\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CurrencyController extends Controller
{

    public function index()
    {
        return view('admin.currency.index');
    }

    public function delete()
    {
        $id = request('id', 0);
        Currency::destroy($id);
        return $this->success('删除成功');
    }

    public function list()
    {
        $limit = request('limit', 10);
        $list = Currency::paginate($limit);
        return $this->layuiPageData($list);
    }

    public function add()
    {
//        abort(403, '添加功能已关闭');
        $account_types = AccountType::where('is_display', AccountType::STATUS_ON)->get();
        return view('admin.currency.add', [
            'account_types' => $account_types
        ]);
    }

    public function save(CurrencySaveRequest $request)
    {
        $data = $request->validationData();
        $data = array_map(function ($item) {
            if (is_array($item)) {
                $item = array_values($item);
            }
            return $item;
        }, $data);
        $currency = Currency::create($data);
        return $this->success('添加成功');
    }

    public function edit()
    {
        $id = request('id', 0);
        $currency = Currency::find($id);
        $account_types = AccountType::where('is_display', AccountType::STATUS_ON)->get();

        return view('admin.currency.edit', [
            'currency' => $currency,
            'account_types' => $account_types
        ]);
    }

    public function update(CurrencyUpdateRequest $request)
    {
        $id = $request->input('id', 0);
        $data = $request->validationData();

        $currency = Currency::findOrFail($id);
        $currency->update($data);

        return $this->success('更改成功');
    }

    public function executeCurrency()
    {
        $id = request('id', 0);

        $currency = Currency::findOrFail($id);

        if (!$currency->currencyProtocols()->exists()) {
            return $this->error('请至设置币种链上协议后再上币');
        }

        Artisan::queue('account:executeCurrency', [
            'currency_id' => $id
        ])->onQueue('executeCurrency');

        return $this->success('已经将申请放入队列,请稍候');
    }

}
