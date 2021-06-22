<?php

namespace App\Http\Requests\Api\Otc;

use App\Http\Requests\BaseRequest;
use App\Models\Otc\OtcDetail;
use App\Models\User\User;

class DetailDeferRequest extends BaseRequest
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
            $detail_id = $this->input('detail_id', 0);
            $user = User::getUser();
            $otc_detail = OtcDetail::findOrFail($detail_id);
            if (empty($otc_detail)) {
                return $validator->errors()->add('exists', __('otc.交易不存在'));
            }
            // 限定只有卖家才能维权
            if ($user->id != $otc_detail->sell_user_id) {
                return $validator->errors()->add('grant', __('otc.只有卖方才能进行延期操作'));
            }
            if ($otc_detail->status != 1) {
                return $validator->errors()->add('status', __('otc.当前状态不允许申请延期确认'));
            }
        });
    }
}
