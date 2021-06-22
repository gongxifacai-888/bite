<?php
/*
 * @Descripttion:
 * @version:
 * @Author: sueRimn
 * @Date: 2020-03-18 14:05:27
 * @LastEditors: sueRimn
 * @LastEditTime: 2020-05-26 09:59:27
 */

namespace App\Http\Requests\Api\Otc;

use App\Http\Requests\BaseRequest;
use App\Models\Otc\OtcDetail;
use App\Models\User\User;

class DetailPayRequest extends BaseRequest
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
            'detail_id' => 'required|integer|gt:0',
            'pay_voucher' => ['required', 'string', 'min:10'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 判断权限：未支付的才能支付，只有买方才能支付
            $detail_id = $this->input('detail_id', 0);
            $otc_detail = OtcDetail::find($detail_id);
            $user = User::getUser();
            if (empty($otc_detail)) {
                return $validator->errors()->add('grant', __('otc.交易不存在'));
            }
            if ($otc_detail->status != 0) {
                return $validator->errors()->add('status', __('otc.当前交易状态不允许支付'));
            }
            if ($otc_detail->buy_user_id != $user->id) {
                return $validator->errors()->add('grant', __('otc.只有买家才能支付'));
            }
        });
    }
}
