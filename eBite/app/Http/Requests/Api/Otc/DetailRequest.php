<?php

namespace App\Http\Requests\Api\Otc;

use App\Exceptions\ValidatorException;
use App\Http\Requests\BaseRequest;
use App\Models\Otc\{OtcDetail, OtcMaster, Seller};
use App\Models\User\{User, UserPayway};

class DetailRequest extends BaseRequest
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
            'master_id' => 'required|integer|gt:0',
            'number' => 'required|numeric|gt:0',
        ];
    }

    public function attributes()
    {
        $lang_file_name = 'otc';
        $fields = [
            'master_id',
            'number',
        ];
        foreach ($fields as $key => $field) {
            $fields_name[$field] = __($lang_file_name . '.' . $field);
        }
        return $fields_name;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            try {
                $user = User::getUser();
                $master_id = $this->input('master_id', 0);
                $number = $this->input('number', 0);
                $price = $this->input('price', 0);
                /** @var \App\Models\Otc\OtcMaster $otc_master */
                $otc_master = OtcMaster::lockForUpdate()->find($master_id);
                if (empty($otc_master)) {
                    throw (new ValidatorException(__("validation.otc.商家otc挂单不存在")))->setField('master');
                }
                $seller = Seller::find($otc_master->seller_id ?? 0);
                if (empty($seller)) {
                    throw (new ValidatorException(__("validation.otc.商家不存在")))->setField('seller');
                }
                if ($seller->user_id == $user->id) {
                    throw (new ValidatorException(__("validation.otc.您不能和自己交易")))->setField('grant');
                }
                if ($otc_master->status != 1) {
                    throw (new ValidatorException(__("validation.otc.商家挂单交易状态异常")))->setField('status');
                }
                if (bc($otc_master->remain_qty, '<=', '0')) {
                    throw (new ValidatorException(__("validation.otc.商家挂单可交易数量为空")))->setField('remain_qty');
                }
                if (bc($otc_master->remain_qty, '<', $number)) {
                    throw (new ValidatorException(
                        __("validation.otc.商家挂单可交易数量不足,当前剩余", [
                            'remain_qty' => $otc_master->remain_qty
                        ])
                    ))->setField('remain_qty');
                }
                if(bc($price, '>', 0) && bc($price, '!=', $otc_master->price)) {
                    throw (new ValidatorException(__("validation.otc.商家发布挂单价格有变化,请刷新后重试")))->setField('price');
                }
                if ($otc_master->way == 'BUY') {
                    $user_way = 'sell';
                    // 检测用户是否已经设置收款方式
                    $not_set_payway = UserPayway::where('user_id', $user->id)->doesntExist();
                    if ($not_set_payway) {
                        throw (new ValidatorException(__("validation.otc.请先设置收付款信息")))->setField('payway');
                    }
                    // 检测用户的支付方式与商家的是否匹配
                    $user_has_payway = UserPayway::where('user_id', $user->id)
                        ->whereIn('payway_id', $otc_master->payways->pluck('id')->all())
                        ->exists();
                    if (!$user_has_payway) {
                        throw (new ValidatorException(__("validation.otc.您的收付款信息与商家挂单不匹配,无法交易")))->setField('payway');
                    }
                } else {
                    $user_way = 'buy';
                }
                // 如果已经和该挂单有过交易且未完成，则不允许再交易
                $has_incomplete_deal = OtcDetail::where("{$user_way}_user_id", $user->id)
                    ->where('master_id', $master_id) // 仅限制该挂单
                    ->whereNotIn('status', [OtcDetail::STATUS_CANCELED, OtcDetail::STATUS_CONFIRMED])
                    ->exists();
                if ($has_incomplete_deal) {
                    throw (new ValidatorException(__("validation.otc.您与该商家有交易未完成,请先完成再操作")))->setField('master');
                }
                // 最大最小交易量检测
                if (bc($otc_master->min_number, '>', 0) && bc($otc_master->remain_qty, '<=', $otc_master->min_number)) {
                    // 当剩余数量小于最小交易量时，必须交易完
                    if (bc($number, '!=', $otc_master->remain_qty)) {
                        throw (new ValidatorException(
                            __("validation.otc.交易数量只能是", [
                                'remain_qty' => $otc_master->remain_qty,
                            ])
                        ))->setField('number');
                    }
                } else {
                    if (bc($otc_master->min_number, '>', 0) && bc($number, '<', $otc_master->min_number)) {
                        throw (new ValidatorException(
                            __("validation.otc.交易数量不能小于", [
                                'min_number' => $otc_master->min_number,
                            ])
                        ))->setField('number');
                    }
                    if (bc($otc_master->max_number, '>', 0) && bc($number, '>', $otc_master->max_number)) {
                        throw (new ValidatorException(
                            __("validation.otc.交易数量不能超过", [
                                'max_number' => $otc_master->max_number,
                            ])
                        ))->setField('grant');
                    }
                }
            } catch (ValidatorException $ex) {
                $validator->errors()->add($ex->getField(), $ex->getMessage());
            } catch (\Throwable $th) {
               throw $th;  
            }
        });
    }
}
