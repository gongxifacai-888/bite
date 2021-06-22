<?php
/*
 * @Descripttion: 
 * @version: 
 * @Author: sueRimn
 * @Date: 2020-03-18 14:05:27
 * @LastEditors: sueRimn
 * @LastEditTime: 2020-05-26 10:02:19
 */

namespace App\Http\Requests\Api\Otc;

use App\Http\Requests\BaseRequest;
use App\Models\Otc\OtcDetail;
use App\Models\User\User;

class DetailArbitrateRequest extends BaseRequest
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

    public function withValidator(\Illuminate\Validation\Validator $validator)
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            $user = User::getUser();
            $detail_id = $this->input('detail_id', 0);
            $otc_detail = OtcDetail::find($detail_id);
            // 限定只有卖家才能维权
            if ($user->id != $otc_detail->sell_user_id) {
                return $validator->errors()->add('grant', __('otc.只有卖方才能发起维权'));
            }
        });
    }
}
