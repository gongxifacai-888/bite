<?php
/*
 * @Descripttion: 
 * @version: 
 * @Author: sueRimn
 * @Date: 2020-03-18 14:05:27
 * @LastEditors: sueRimn
 * @LastEditTime: 2020-05-26 10:01:05
 */

namespace App\Http\Requests\Api\Otc;

use App\Http\Requests\BaseRequest;
use App\Models\Otc\OtcDetail;
use App\Models\User\User;

class DetailConfirmRequest extends BaseRequest
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
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 只有卖家才能进行确认
            $detail_id = $this->input('detail_id', 0);
            $otc_detail = OtcDetail::lockForUpdate()->find($detail_id);
            if (empty($otc_detail)) {
                return $validator->errors()->add('exists', __('otc.交易不存在'));
            }
            $user = User::getUser();
            if ($otc_detail->sell_user_id != $user->id) {
                return $validator->errors()->add('grant', __('otc.只有卖方才能进行确认'));
            }
            $detail_id = $this->input('detail_id', 0);
            if (!in_array($otc_detail->status, [OtcDetail::STATUS_PAYED, OtcDetail::STATUS_DEFERRED, OtcDetail::STATUS_ARBITRATED])) {
                return $validator->errors()->add('status', __('otc.当前状态交易不能确认'));
            }
        });
    }
}
