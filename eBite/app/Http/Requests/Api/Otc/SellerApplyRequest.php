<?php
/*
 * @Descripttion: 
 * @version: 
 * @Author: sueRimn
 * @Date: 2020-03-18 14:05:27
 * @LastEditors: sueRimn
 * @LastEditTime: 2020-05-26 09:53:55
 */

namespace App\Http\Requests\Api\Otc;

use App\Http\Requests\BaseRequest;
use App\Models\Otc\{SellerApply, SellerCurrency};
use App\Models\User\User;

class SellerApplyRequest extends BaseRequest
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
            'name' => 'required|string|min:1|unique:sellers',
            'currencies' => 'required|array|min:1',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 检测用户身份是否是商家
            $user = User::getUser();
            if ($user->isSeller(false)) {
                // 身份异常的商家也不能再申请
                return $validator->errors()->add('grant', __('otc.您已是商家,请勿重复申请'));
            }
            // 检测用户是否有未审核的申请
            $has_apply = SellerApply::where('user_id', $user->id)
                ->where('status', 0)
                ->exists();
            if ($has_apply) {
                return $validator->errors()->add('grant', __('otc.您的申请还未审核,请耐心等待,不要重复提交'));
            }
            //币种验证
            $currencies = $this->input('currencies', []);
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
