<?php

namespace App\Http\Controllers\Admin;

use App\Models\Currency\CurrencyMatch;
use App\Models\Lever\LeverTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeverTransactionController extends Controller
{

    public function Leverdeals_show()
    {
        $matches = CurrencyMatch::where('open_lever', 1)->get();
        return view("admin.leverdeals.list", [
            'matches' => $matches,
        ]);
    }

    //合约交易
    public function Leverdeals(Request $request)
    {
        $limit             = $request->input("limit", 10);
        $match_id          = $request->input('match_id', 0);
        $uid               = $request->input("uid", '');
        $mobile            = $request->input("mobile", '');
        $email             = $request->input("email", '');
        $status            = $request->input("status", -1);
        $type              = $request->input("type", 0);
        $start_time        = $request->input("start_time", '');
        $end_time          = $request->input("end_time", '');
        $quote_currency_id = 0;
        $base_currency_id  = 0;
        if ($match_id > 0) {
            $match             = CurrencyMatch::find($match_id);
            $quote_currency_id = $match->quote_currency_id ?? 0;
            $base_currency_id  = $match->base_currency_id ?? 0;
        }
        $order_list = LeverTransaction::when($quote_currency_id > 0, function ($query) use ($quote_currency_id) {
            $query->where('quote_currency_id', $quote_currency_id);
        })->when($base_currency_id > 0, function ($query) use ($base_currency_id) {
            $query->where('base_currency_id', $base_currency_id);
        })->when($uid != '', function ($query) use ($uid) {
            $query->whereHas('user', function ($query) use ($uid) {
                $query->where('uid', $uid);
            });
        })->when($email, function ($query, $email) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            });
        })->when($mobile, function ($query, $mobile) {
            $query->whereHas('user', function ($query) use ($mobile) {
                $query->where('mobile', $mobile);
            });
        })->when($type > 0, function ($query) use ($type) {
            $query->where('type', $type);
        })->when($status <> -1, function ($query) use ($status) {
            $query->where('status', $status);
        })->when($start_time != '', function ($query) use ($start_time) {
            $query->where('create_time', '>=', strtotime($start_time));
        })->when($end_time != '', function ($query) use ($end_time) {
            $query->where('create_time', '<=', strtotime($end_time));
        })->orderBy('id', 'desc')
            ->paginate($limit);

        $order_list->each(function($order){
            $order->append('uid','symbol');
        });

        return $this->layuiPageData($order_list);
    }

    //导出合约交易 团队所有订单excel
    public function csv(Request $request)
    {

        //        $limit = $request->input("limit", "");
        $id       = $request->input("id", 0);
        $username = $request->input("phone", '');
        $status   = $request->input("status", 10);
        $type     = $request->input("type", 0);

        $start = $request->input("start", '');
        $end   = $request->input("end", '');
        //        var_dump($id);die;
        $where = [];
        if ($id > 0) {
            $where[] = ['lever_transaction.id', '=', $id];
        }
        //        var_dump($where);die;
        if (!empty($username)) {
            $s = DB::table('users')->where('account_number', $username)->first();
            if ($s !== null) {
                $where[] = ['lever_transaction.user_id', '=', $s->id];
            }
        }

        if ($status != -1 && in_array($status, [LeverTransaction::STATUS_ENTRUST, LeverTransaction::WAY_BUY, LeverTransaction::STATUS_CLOSED, LeverTransaction::STATUS_CANCEL, LeverTransaction::STATUS_CLOSING])) {
            $where[] = ['lever_transaction.status', '=', $status];
        }

        if ($type > 0 && in_array($type, [1, 2])) {
            $where[] = ['type', '=', $type];
        }
        if (!empty($start) && !empty($end)) {
            $where[] = ['lever_transaction.create_time', '>', strtotime($start . ' 0:0:0')];
            $where[] = ['lever_transaction.create_time', '<', strtotime($end . ' 23:59:59')];
        }

        $order_list = LeverTransaction::leftjoin("users", "lever_transaction.user_id", "=", "users.id")
            ->select("lever_transaction.*", "users.phone")
            ->whereIn('lever_transaction.status', [
                LeverTransaction::STATUS_ENTRUST,
                LeverTransaction::WAY_BUY,
                LeverTransaction::STATUS_CLOSED,
                LeverTransaction::STATUS_CANCEL,
                LeverTransaction::STATUS_CLOSING
            ])
            ->where($where)
            ->get();

        foreach ($order_list as $key => $value) {
            $order_list[$key]["create_time"]      = date("Y-m-d H:i:s", $value->create_time);
            $order_list[$key]["transaction_time"] = date("Y-m-d H:i:s", substr($value->transaction_time, 0, strpos($value->transaction_time, '.')));
            $order_list[$key]["update_time"]      = date("Y-m-d H:i:s", substr($value->update_time, 0, strpos($value->update_time, '.')));
            $order_list[$key]["handle_time"]      = date("Y-m-d H:i:s", substr($value->handle_time, 0, strpos($value->handle_time, '.')));
            $order_list[$key]["complete_time"]    = date("Y-m-d H:i:s", substr($value->complete_time, 0, strpos($value->complete_time, '.')));
        }

        $data = $order_list;

        return Excel::create('合约交易', function ($excel) use ($data) {
            $excel->sheet('合约交易', function ($sheet) use ($data) {
                $sheet->cell('A1', function ($cell) {
                    $cell->setValue('ID');
                });
                $sheet->cell('B1', function ($cell) {
                    $cell->setValue('用户名');
                });
                $sheet->cell('C1', function ($cell) {
                    $cell->setValue('交易手续费');
                });
                $sheet->cell('D1', function ($cell) {
                    $cell->setValue('隔夜费金额');
                });
                $sheet->cell('E1', function ($cell) {
                    $cell->setValue('交易类型');
                });
                $sheet->cell('F1', function ($cell) {
                    $cell->setValue('当前状态');
                });
                $sheet->cell('G1', function ($cell) {
                    $cell->setValue('原始价格');
                });
                $sheet->cell('H1', function ($cell) {
                    $cell->setValue('开仓价格');
                });
                $sheet->cell('I1', function ($cell) {
                    $cell->setValue('当前价格');
                });


                $sheet->cell('J1', function ($cell) {
                    $cell->setValue('手数');
                });
                $sheet->cell('K1', function ($cell) {
                    $cell->setValue('倍数');
                });
                $sheet->cell('L1', function ($cell) {
                    $cell->setValue('初始保证金');
                });
                $sheet->cell('M1', function ($cell) {
                    $cell->setValue('当前可用保证金');
                });
                $sheet->cell('N1', function ($cell) {
                    $cell->setValue('创建时间');
                });
                $sheet->cell('O1', function ($cell) {
                    $cell->setValue('价格刷新时间');
                });
                $sheet->cell('P1', function ($cell) {
                    $cell->setValue('平仓时间');
                });
                $sheet->cell('Q1', function ($cell) {
                    $cell->setValue('完成时间');
                });

                if (!empty($data)) {
                    foreach ($data as $key => $value) {
                        if ($value['type'] == 1) {
                            $value['type'] = "买入";
                        } else {
                            $value['type'] = "卖出";
                        }
                        if ($value['status'] == 0) {
                            $value['status'] = "挂单中";
                        } elseif ($value['status'] == 1) {
                            $value['status'] = "交易中";
                        } elseif ($value['status'] == 2) {
                            $value['status'] = "平仓中";
                        } elseif ($value['status'] == 3) {
                            $value['status'] = "已平仓";
                        } elseif ($value['status'] == 4) {
                            $value['status'] = "已撤单";
                        }

                        $i = $key + 2;
                        $sheet->cell('A' . $i, $value['id']);
                        $sheet->cell('B' . $i, $value['phone']);
                        $sheet->cell('C' . $i, $value['trade_fee']);
                        $sheet->cell('D' . $i, $value['overnight_money']);
                        $sheet->cell('E' . $i, $value['type']);
                        $sheet->cell('F' . $i, $value['status']);
                        $sheet->cell('G' . $i, $value['origin_price']);
                        $sheet->cell('H' . $i, $value['price']);
                        $sheet->cell('I' . $i, $value['update_price']);

                        $sheet->cell('J' . $i, $value['share']);
                        $sheet->cell('K' . $i, $value['multiple']);
                        $sheet->cell('L' . $i, $value['origin_caution_money']);
                        $sheet->cell('M' . $i, $value['caution_money']);
                        $sheet->cell('N' . $i, $value['create_time']);
                        $sheet->cell('O' . $i, $value['update_time']);
                        $sheet->cell('P' . $i, $value['handle_time']);
                        $sheet->cell('Q' . $i, $value['complete_time']);
                    }
                }
            });
        })->download('xlsx');
    }

    /**
     * 后台强制平仓
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function close(Request $request)
    {
        $id = $request->get("id");
        if (empty($id)) {
            return $this->error("参数错误");
        }

        DB::beginTransaction();
        try {
            $lever_transaction = LeverTransaction::lockForupdate()->find($id);
            if (empty($lever_transaction)) {
                throw new \Exception("数据未找到");
            }

            if ($lever_transaction->status != LeverTransaction::STATUS_TRANSACTION) {
                throw new \Exception("交易状态异常,请勿重复提交");
            }
            $return = LeverTransaction::leverClose($lever_transaction, LeverTransaction::CLOSED_BY_ADMIN);
            if (!$return) {
                throw new \Exception("平仓失败,请重试");
            }
            DB::commit();
            return $this->success("操作成功");
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getFile() . $ex->getLine() . $ex->getMessage());
        }
    }
}
