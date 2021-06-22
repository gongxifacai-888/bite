<?php

namespace App\Http\Requests\Api\Otc;

use App\Http\Requests\BaseRequest;
use App\Models\Currency\Currency;
use App\Models\System\Payway;
use App\Models\User\{User, UserPayway};

class MasterRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = User::getUser();
        // 检测身份
        if (empty($user) || !$user->isSeller()) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'way' => 'required|in:BUY,SELL',
            'currency_id' => 'required|integer',
            'area_id' => 'required|integer',
            'price' => 'required|numeric|gt:0',
            'total_qty' => 'required|numeric|gt:0',
            'min_number' => 'required|numeric|gte:0',
            'max_number' => 'required|numeric|gte:0',
            'payways' => 'required|array|min:1',
            'auto_start' => 'sometimes|in:0,1',
        ];
    }

    public function attributes()
    {
        $lang_file_name = 'otc';
        $fields = [
            'way',
            'currency_id',
            'area_id',
            'price',
            'total_qty',
            'min_number',
            'max_number',
            'payways',
        ];
        foreach ($fields as $key => $field) {
            $fields_name[$field] = __($lang_file_name . '.' . $field);
        }
        return $fields_name;
    }

    public function withValidator(\Illuminate\Validation\Validator $validator)
    {
        $validator->after(function ($validator) {
            $user = User::getUser();
            $seller = $user->seller;
            $payways = $this->input('payways', []);
            $min_number = $this->input('min_number', 0);
            $max_number = $this->input('max_number', 0);
            // 验证最大最小交易量是否合理
            if (bc($max_number, '>', 0) && bc($max_number, '<', $min_number)) {
                $validator->errors()->add('max_number', __('otc.最大交易数量不能小于最小交易数量'));
            }
            $sys_payway_ids = Payway::pluck('id')->all();
            $payways = array_filter($payways, function ($item) use ($sys_payway_ids) {
                return !is_null($item) && !empty($item) && in_array($item, $sys_payway_ids);
            });
            // 验证选择的收款信息是否设置
            if (empty($payways)) {
                $validator->errors()->add('payways', __('otc.您至少应选择一种支付/收款方式'));
            }
            $user_payways = UserPayway::where('user_id', $user->id)->get();
            if ($user_payways->isEmpty()) {
                $validator->errors()->add('payways', __('otc.您尚未设置付款/收款信息！'));
            }
            $user_payways_ids = $user_payways->pluck('payway_id')->all();
            $diff = array_diff($payways, $user_payways_ids);
            if (count($diff) > 0) {
                $diff_payways = Payway::whereIn('id', $diff)->pluck('code')->all();
                $payways_code = array_map(function ($item) {
                    return __("otc.{$item}");
                }, $diff_payways);
                $payways_code = implode(',', $payways_code);
                $validator->errors()->add('payways', __('otc.您还没有设置[:payways]支付/收款信息', [
                    'payways' => $payways_code,
                ]));
            }
            // 验证是否具有某一个币种的买入、卖出的发布权限
            $currencies = $seller->sellerCurrencies;
            $currency_id = $this->input('currency_id', 0);
            $way = strtolower($this->input('way', ''));
            $has_currency = $currencies->contains('currency_id', $currency_id);
            $sell_currency = $currencies->where('currency_id', $currency_id)->first();
            $currency = Currency::findOrFail($currency_id);
            if (!$has_currency || empty($sell_currency) || $sell_currency->{"{$way}_status"} <= 0) {
                $validator->errors()->add('grant', __("otc.抱歉，您没有权限投递：投币方式！", [
                    'way' => __("otc.$way"),
                    'coin' => $currency->code,
                ]));
            }
        });
    }
}
