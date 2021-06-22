<?php
/*
 * @Descripttion: 
 * @version: 
 * @Author: sueRimn
 * @Date: 2020-03-18 14:05:27
 * @LastEditors: sueRimn
 * @LastEditTime: 2020-05-26 10:01:33
 */

namespace App\Http\Requests\Api\Otc;

use App\Http\Requests\BaseRequest;
use App\Models\Otc\OtcDetail;
use App\Models\User\User;

class DetailCancelRequest extends BaseRequest
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
            'detail_id' => 'required|integer|gt:0|exists:otc_details,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $detail_id = $this->input('detail_id', 0);
            $user = User::getUser();
            $otc_detail = OtcDetail::find($detail_id);
            if (empty($otc_detail)) {
                return $validator->errors()->add('status', __('otc.交易不存在'));
            }
            // 限定只有买家才能取消
            if ($user->id != $otc_detail->buy_user_id) {
                return $validator->errors()->add('grant', __('otc.只有买方才有权限取消'));
            }
            $otc_detail = $otc_detail->lockForUpdate()->find($detail_id);
            if (!in_array($otc_detail->status, [
                OtcDetail::STATUS_OPENED,
                OtcDetail::STATUS_DEFERRED,
                OtcDetail::STATUS_ARBITRATED,
            ])) {
                return $validator->errors()->add('status', __('otc.当前状态交易不能取消'));
            }
        });
    }
}
