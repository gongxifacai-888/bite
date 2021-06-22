<?php

namespace App\Http\Requests\Admin\Currency;

use App\Http\Requests\BaseRequest;
use App\Models\Currency\Currency;

class CurrencySaveRequest extends BaseRequest
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
            'code' => 'required',
            'logo' => 'required',
            'desc' => 'required',

            'is_quote' => 'required|boolean',
            'accounts_display' => 'array',

            'draw_rate' => 'required|numeric|min:0|max:1',
            'open_draw' => 'required|boolean',
            // 'draw_min' => 'required|int|min:0',
            'draw_max' => 'required|int',
        ];
    }

    public function attributes()
    {
        $attributes = [
            'code' => '币种代码',
            'logo' => 'LOGO',
            'desc' => '简介',

            'is_quote' => '是否计价币',
            'accounts_display' => '账户展示',

            'draw_rate' => '提币费率',
            'open_draw' => '开启提币',
            'draw_min' => '最小提币数量',
            'draw_max' => '最大提币数量',
        ];
        return $attributes;
    }

    /**
     *  配置验证器实例。
     *
     * @param \Illuminate\Validation\Validator $validator
     *
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $code = $this->input('code', '');

            $exists = Currency::where('code', $code)->exists();
            if ($exists) {
                return $validator->errors()->add('code', '此币种已存在');
            }
        });
    }

}
