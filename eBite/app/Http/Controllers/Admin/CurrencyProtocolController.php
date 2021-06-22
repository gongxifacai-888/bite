<?php


namespace App\Http\Controllers\Admin;

use App\BlockChain\BlockChain;
use App\Logic\GoChain;
use App\Models\Chain\ChainProtocol;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyProtocol;
use App\Exceptions\ThrowException;

/**币种协议控制器
 * Class CurrencyProtocolController
 *
 * @package App\Http\Controllers\Admin
 */
class CurrencyProtocolController extends Controller
{
    public function index()
    {
        $currency_id = request('currency_id', 0);
        return view('admin.currencyProtocol.index', [
            'currency_id' => $currency_id
        ]);
    }

    public function list()
    {
        $limit = request('limit', 10);
        $currency_id = request('currency_id', 0);

        $currency_protocols = CurrencyProtocol::when($currency_id, function ($query, $currency_id) {
            $query->where('currency_id', $currency_id);
        })->paginate($limit);

        $currency_protocols->getCollection()->each(function ($currencyProtocol) {
            /**@var CurrencyProtocol $currencyProtocol * */
            $currencyProtocol->append('chain_protocol_code', 'currency_code');
        });

        return $this->layuiPageData($currency_protocols);
    }

    public function add()
    {
//        abort(403,'添加功能已关闭');
        $currencies = Currency::get();
        $chain_protocols = ChainProtocol::get();

        return view('admin.currencyProtocol.add', [
            'currencies' => $currencies,
            'chain_protocols' => $chain_protocols,
        ]);
    }

    public function save()
    {
        $data = request()->all();
        $data['token_address'] = $data['token_address'] ?: '';
        //检查协议是否重复
        $exists = CurrencyProtocol::where('currency_id', $data['currency_id'])
            ->where('chain_protocol_id', $data['chain_protocol_id'])
            ->exists();
        if ($exists) {
            throw new ThrowException('此币种已存在该协议');
        }

        $currencyProtocol = CurrencyProtocol::create($data);
        return $this->success('添加成功');
    }

    public function edit()
    {
        $id = request('id', 0);

        $currencies = Currency::get();
        $chain_protocols = ChainProtocol::get();

        $currencyProtocol = CurrencyProtocol::findOrFail($id);
        return view('admin.currencyProtocol.edit', [
            'currencyProtocol' => $currencyProtocol,
            'currencies' => $currencies,
            'chain_protocols' => $chain_protocols,
        ]);
    }

    public function update()
    {
        $id = request('id', 0);
        $data = request()->all();
        $data['token_address'] = $data['token_address'] ?: '';

        //检查协议是否重复
        $exists = CurrencyProtocol::where('currency_id', $data['currency_id'])
            ->where('chain_protocol_id', $data['chain_protocol_id'])
            ->where('id', '<>', $id)
            ->exists();
        if ($exists) {
            throw new ThrowException('此币种已存在该协议');
        }

        $currencyProtocol = CurrencyProtocol::findOrFail($id)->update($data);
        return $this->success('更新成功');
    }

    /**编辑入金总账号
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editInAddress()
    {
        $id = request('id', 0);

        $currencyProtocol = CurrencyProtocol::findOrFail($id);
        return view('admin.currencyProtocol.editInAddress', [
            'currencyProtocol' => $currencyProtocol,
        ]);
    }

    /**更新入金总账号
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function updateInAddress()
    {
        $id = request('id', 0);
        $data = request()->except(['code']);
        $code = request('code', 0);

        $address = request('in_address', '');

        $currencyProtocol = CurrencyProtocol::findOrFail($id);

        $coin = $currencyProtocol->currency()->value('code');
        $chain = $currencyProtocol->chainProtocol()->value('code');
        $response = GoChain::updateInAddress($coin, $chain, $address, $code);

        if (!isset($response['code']) || $response['code'] != 0) {
            return $this->error($response['msg'] ?? 'GO更新地址错误');
        }

        $currencyProtocol->update($data);

        return $this->success('更新成功');
    }

    /**编辑出金总账号
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editOutAddress()
    {
        $id = request('id', 0);

        $currencyProtocol = CurrencyProtocol::findOrFail($id);
        return view('admin.currencyProtocol.editOutAddress', [
            'currencyProtocol' => $currencyProtocol,
        ]);
    }

    /**更新出金总账号
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateOutAddress()
    {
        $id = request('id', 0);
        $data = request()->except(['code']);
        $code = request('code', 0);

        $address = request('out_address', '');

        $currencyProtocol = CurrencyProtocol::findOrFail($id);

        $coin = $currencyProtocol->currency()->value('code');
        $chain = $currencyProtocol->chainProtocol()->value('code');
        $response = GoChain::updateOutAddress($coin, $chain, $address, $code);

        if (!isset($response['code']) || $response['code'] != 0) {
            return $this->error($response['msg'] ?? 'GO更新地址错误');
        }

        $currencyProtocol->update($data);

        return $this->success('更新成功');
    }
}
