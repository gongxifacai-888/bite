<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Events\Otc\{DealCancelEvent, DealConfirmedEvent, DealDeferEvent, DealPaidEvent, DealArbitrateEvent, MatchDealEvent};
use App\Http\Requests\Api\Otc\{DetailArbitrateRequest, DetailCancelRequest, DetailRequest, DetailConfirmRequest, DetailDeferRequest, DetailPayRequest, MasterRequest};
use App\Models\Otc\{OtcDetail, OtcMaster, SellerCurrency};
use App\Models\User\User;

class OtcController extends Controller
{
    /**
     * 商家发布交易
     *
     * @param MasterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postMaster(MasterRequest $request)
    {
        try {
            $user = User::getUser();
            $data = $request->validationData();
            $auto_start = $data['auto_start'] ?? 0;
            if (isset($data['auto_start'])) {
                unset($data['auto_start']);
            }
            $otc_master = OtcMaster::postMaster($user, $data);
            // 是否自动开始
            if ($auto_start == 1) {
                OtcMaster::startMaster($otc_master, $user);
            }
            return $this->success(__('otc.ok'), $otc_master);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 取消挂单
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelMaster(Request $request)
    {
        try {
            $master_id = $request->input('master_id', 0);
            $user = User::getUser();
            $otc_master = OtcMaster::where('seller_id', $user->seller->id)->findOrFail($master_id);
            OtcMaster::cancelMaster($otc_master);
            return $this->success(__('otc.ok'));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 开启挂单交易(商家自已)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function startMaster(Request $request)
    {
        try {
            $master_id = $request->input('master_id', 0);
            $user = User::getUser();
            $otc_master = OtcMaster::where('seller_id', $user->seller->id)->findOrFail($master_id);
            OtcMaster::startMaster($otc_master);
            return $this->success(__('otc.ok'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $th) {
            $ids = implode(',', $th->getIds());
            return $this->error("{$th->getModel()}对应信息{$ids}未找到");
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 暂停交易(商家自已)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pauseMaster(Request $request)
    {
        try {
            $master_id = $request->input('master_id', 0);
            $user = User::getUser();
            $otc_master = OtcMaster::where('seller_id', $user->seller->id)->findOrFail($master_id);
            OtcMaster::pauseMaster($otc_master);
            return $this->success(__('otc.ok'));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 匹配挂单(用户)
     *
     * @param OtcDetailRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function matchMaster(DetailRequest $request)
    {
        try {
            $user = User::getUser();
            $master_id = $request->input('master_id', 0);
            $number = $request->input('number', 0);
            $otc_master = OtcMaster::findOrFail($master_id);
            $otc_detail = OtcDetail::matchMaster($otc_master, $user, $number);
            // 产生一个交易匹配的事件
            event(new MatchDealEvent($otc_detail));
            return $this->success(__('otc.ok'), $otc_detail->refresh());
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 交易支付(买方)
     *
     * @param OtcDetailPayRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pay(DetailPayRequest $request)
    {
        try {
            $user = User::getUser();
            $detail_id = $request->input('detail_id', 0);
            $pay_voucher = $request->input('pay_voucher', '');
            $otc_detail = OtcDetail::lockForUpdate()->findOrFail($detail_id);
            $otc_detail = OtcDetail::pay($otc_detail, $user, $pay_voucher);
            // 产生一个交易支付事件
            event(new DealPaidEvent($otc_detail));
            return $this->success(__('otc.ok'));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 交易确认(卖方)
     *
     * @param OtcDetailConfirmRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm(DetailConfirmRequest $request)
    {
        try {
            $user = User::getUser();
            $detail_id = $request->input('detail_id', 0);
            $otc_detail = OtcDetail::lockForUpdate()->findOrFail($detail_id);
            throw_if(!in_array($otc_detail->status, [
                OtcDetail::STATUS_PAYED,
                OtcDetail::STATUS_DEFERRED,
                OtcDetail::STATUS_ARBITRATED,
            ]), new \Exception(__("otc.当前状态不能确认")));
            $otc_detail = OtcDetail::confirm($otc_detail, $user);
            // 产生一个交易成功事件
            event(new DealConfirmedEvent($otc_detail));
            return $this->success(__('otc.ok'));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 交易延期确认(卖方)
     *
     * @param OtcDetailDeferRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function defer(DetailDeferRequest $request)
    {
        try {
            $user = User::getUser();
            $detail_id = $request->input('detail_id', 0);
            $otc_detail = OtcDetail::where('sell_user_id', $user->id)
                ->where('status', OtcDetail::STATUS_PAYED)
                ->lockForUpdate()
                ->findOrFail($detail_id);
            $otc_detail = OtcDetail::defer($otc_detail, $user);
            // 产生一个交易延期事件
            event(new DealDeferEvent($otc_detail));
            return $this->success(__('otc.ok'));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 申请交易维权(卖方)
     *
     * @param OtcDetailArbitrateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function arbitrate(DetailArbitrateRequest $request)
    {
        try {
            $user = User::getUser();
            $detail_id = $request->input('detail_id', 0);
            $otc_detail = OtcDetail::where('sell_user_id', $user->id)
                ->where('status', OtcDetail::STATUS_DEFERRED)
                ->lockForUpdate()
                ->findOrFail($detail_id);
            $otc_detail = OtcDetail::arbitrate($otc_detail, $user);
            // 产生一个交易维权事件
            event(new DealArbitrateEvent($otc_detail));
            return $this->success(__('otc.ok'));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 取消交易(限买方)
     *
     * @param OtcDetailCancelRequest $request
     * @return Illuminate\Http\JsonResponse
     */
    public function cancel(DetailCancelRequest $request)
    {
        try {
            $user = User::getUser();
            $detail_id = $request->input('detail_id', 0);
            $otc_detail = OtcDetail::lockForUpdate()->findOrFail($detail_id);
            throw_if(!in_array($otc_detail->status, [
                OtcDetail::STATUS_OPENED,
                OtcDetail::STATUS_DEFERRED,
                OtcDetail::STATUS_ARBITRATED,
            ]), new \Exception(__("otc.当前状态不能取消")));
            $otc_detail = OtcDetail::cancel($otc_detail, $user);
            // 产生一个交易维权事件
            event(new DealCancelEvent($otc_detail));
            return $this->success(__('otc.ok'));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    public function otcCurrencies()
    {
        try {
            $currencies = SellerCurrency::getLegalCurrencies();
            return $this->success(__(''), $currencies);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 商家OTC支持的币种
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sellerCurrencies(Request $request)
    {
        try {
            $user = User::getUser();
            $currency_id = $request->input('currency_id', 0);
            $seller_currencies = SellerCurrency::whereHas('seller', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->when($currency_id > 0, function ($query) use ($currency_id) {
                    $query->where('currency_id', $currency_id);
                })->get();
            return $this->success(__(''), $seller_currencies);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 商家发布的挂单
     *
     * @param Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function sellerMasters(Request $request)
    {
        try {
            $user = User::getUser();
            $limit = $request->input('limit', 10);
            $currency_id = $request->input('currency_id', 0);
            $status = $request->input('status', -1);
            $way = $request->input('way', '');
            throw_unless($user->isSeller(), new \Exception(__('otc.您不是商家或者商家身份异常')));
            $otc_masters = OtcMaster::where('seller_id', $user->seller->id)
                ->when($currency_id > 0, function ($query) use ($currency_id) {
                    $query->where('currency_id', $currency_id);
                })
                ->when($status <> -1, function ($query) use ($status) {
                    $query->where('status', $status);
                })
                ->when($way != '', function ($query) use ($way) {
                    $query->where('way', $way);
                })
                ->orderBy('id', 'desc')
                ->paginate($limit);
            return $this->success(__('otc.请求成功'), $otc_masters);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 商家挂单下交易记录
     *
     * @param Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function masterDetails(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            $master_id = $request->input('master_id', 0);
            $status = $request->input('status', -1);
            $user = User::getUser();
            $otc_master = OtcMaster::where('seller_id', $user->seller->id)->findOrFail($master_id);
            $details = $otc_master->details()->where(function ($query) use ($status) {
                    $status > -1 && $query->where('status', $status);
                })->paginate($limit);
            return $this->success(__('otc.ok'), $details);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 商家发布需求列表
     *
     * @param Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function masters(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            $currency_id = $request->input('currency_id', 0);
            $way = $request->input('way', '');
            /** @var \Illuminate\Pagination\LengthAwarePaginator $otc_masters */
            $otc_masters = OtcMaster::where('status', OtcMaster::STATUS_OPENED)
                ->where('remain_qty', '>', 0)
                ->when($currency_id > 0, function ($query) use ($currency_id) {
                    $query->where('currency_id', $currency_id);
                })
                ->when($way != '', function ($query) use ($way) {
                    $query->where('way', $way);
                })
                ->orderBy('price')
                ->orderBy('id', 'desc')
                ->paginate($limit);
            $items = $otc_masters->getCollection();
            $items->transform(function ($item, $key) {
                $item->addHidden([
                    'user_account',
                    'seller_account',
                    'currency_name'
                ]);
                return $item;
            });
            return $this->success(__('otc.ok'), $otc_masters);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 用户交易信息
     *
     * @param Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function detail(Request $request)
    {
        try {
            $detail_id = $request->input('detail_id', 0);
            $user = User::getUser();
            $otc_detail = OtcDetail::where(function ($query) use ($user) {
                $query->where('buy_user_id', $user->id)->orWhere('sell_user_id', $user->id);
            })->findOrFail($detail_id);
            return $this->success(__('otc.ok'), $otc_detail);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 交易状态追踪
     *
     * @param Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function detailActions(Request $request)
    {
        try {
            $detail_id = $request->input('detail_id', 0);
            $user = User::getUser();
            /** @var \App\Models\Otc\OtcDetail $otc_detail  */
            $otc_detail = OtcDetail::where(function ($query) use ($user) {
                $query->where('buy_user_id', $user->id)->orWhere('sell_user_id', $user->id);
            })->findOrFail($detail_id);
            $otc_actions = $otc_detail->actions()->orderBy('id', 'asc')->get();
            return $this->success(__('otc.ok'), $otc_actions);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 用户交易明细
     *
     * @param Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function userDetails(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            $way = $request->input('way', '');
            $currency_id = $request->input('currency_id', 0);
            $status = $request->input('status', -1);
            $user = User::getUser();
            $otc_details = OtcDetail::where('user_id', $user->id)
                ->when($currency_id > 0, function ($query) use ($currency_id) {
                    $query->where('currency_id', $currency_id);
                })
                ->when($way != '', function ($query) use ($way) {
                    $query->where('way', $way);
                })
                ->when($status >= 0, function ($query) use ($status) {
                    $query->where('status', $status);
                })
                ->orderBy('id', 'desc')
                ->paginate($limit);
            return $this->success(__('otc.ok'), $otc_details);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
