<?php


namespace App\Http\Controllers\Admin;


use App\Logic\Market;
use App\Models\Currency\CurrencyMatch;
use App\Models\Currency\CurrencyQuotation;
use App\Models\Tx\Robot;
use App\Models\User\User;

class RobotController extends Controller
{
    public function index()
    {
        return view('admin.robot.index');
    }

    public function list()
    {
        $robots = Robot::paginate();
        return $this->layuiPageData($robots);
    }

    public function edit()
    {
        $id = request('id', 0);
        $robot = Robot::findOrFail($id);
        $currency_matches = CurrencyMatch::get();
        return view('admin.robot.edit', [
            'robot' => $robot,
            'currency_matches' => $currency_matches,
        ]);
    }

    public function update()
    {
        $id = request('id', 0);
        $data = request()->all();

        if ($data['price'] < 0.0001) {
            //如果小于0.1可能会造成盘口价格负数问题
            return $this->error('价格不能小于0.0001');
        }

        $robot = Robot::find($id);

        // if ($robot->status == Robot::STATUS_ON) {
        //     $close = CurrencyQuotation::where('currency_match_id', $robot->currencyMatch()->value('id'))
        //         ->value('close');
        //     if ($close < ($data['price'] * 0.95)) {
        //         $p = parse_price($close * 1.05, $robot->currencyMatch, 4);
        //         return $this->error("上账幅度过大!,请不要设置上账超过{$p}(5%)");
        //     }
        //     if ($close > ($data['price'] * 1.05)) {
        //         $p = parse_price($close * 0.95, $robot->currencyMatch, 4);
        //         return $this->error("下跌幅度过大!,请不要设置下跌超过{$p}(5%)");
        //     }
        // }
        if ($data['decimal'] > 8) {
            return $this->error('价格小数位数不能大于8');
        }

        if ($data['status'] == Robot::STATUS_ON && $robot->currencyMatch->market_from != CurrencyMatch::ROBOT) {
            return $this->error('此交易对的行情选择不是来自机器人,请先更改此交易对的行情源');
        }

        $robot->update($data);

        return $this->success('操作成功');
    }
}
