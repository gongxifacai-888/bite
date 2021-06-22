<?php
/*
 * @Descripttion: 
 * @version: 
 * @Author: sueRimn
 * @Date: 2020-03-18 14:05:27
 * @LastEditors: sueRimn
 * @LastEditTime: 2020-05-26 09:55:08
 */

namespace App\Http\Requests\Api\Otc;

use App\Http\Requests\BaseRequest;
use App\Models\User\User;
use App\Models\Otc\{SellerCurrencyApply, SellerCurrency};

class SellerCurrencyApplyRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'currencies' => 'required|array|min:1',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 检测用户身份是否已是商家
            $user = User::getUser();
            $currencies = $this->input('currencies', []);
            if (!$user->isSeller()) {
                return $validator->errors()->add('grant', __('otc.您不是商家或商家状态异常,不能申请币种权限'));
            }
            // 检测用户是否有未审核的申请
            $has_apply = SellerCurrencyApply::where('user_id', $user->id)
                ->where('seller_id', $user->seller->id)
                ->where('status', 0)
                ->exists();
            if ($has_apply) {
                return $validator->errors()->add('grant', __('otc.您的申请还未审核,请耐心等待,不要重复提交'));
            }
            // 检测是否已有对应币种的权限
            $has_currency = SellerCurrency::where('seller_id', $user->seller->id)
                ->whereIn('currency_id', $currencies)
                ->exists();
            if ($has_currency) {
                return $validator->errors()->add('grant', __('otc.您已经拥有申请币种的权限,请不要重复申请'));
            }

            $all_currencies = SellerCurrency::getLegalCurrencies();
            $all_currencies_ids = $all_currencies->pluck('id')->all();
            $no_currencies = array_filter($currencies, function ($item) use ($all_currencies_ids) {

                return !is_null($item) && !empty($item) && !in_array($item, $all_currencies_ids);
            });
            if ($no_currencies) {
                $validator->errors()->add('currencies', __('otc.币种信息有误'));
            }
        });
    }
}
