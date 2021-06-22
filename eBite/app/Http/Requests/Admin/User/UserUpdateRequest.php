<?php

namespace App\Http\Requests\Admin\User;

use App\Http\Requests\BaseRequest;
use App\Models\Currency\Currency;
use App\Models\System\Area;
use App\Models\User\User;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends BaseRequest
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
            'email' => 'required',
            'password' => 'required|alpha_num|min:6|max:16',
            'register_type' => 'required|in:email,mobile',
            'invite_code' => 'sometimes|nullable|size:6|alpha',
            'area_id' => 'required|required|numeric',
        ];
    }

    public function attributes()
    {
        $attributes = [
            'account' => '账号',
            'password' => '密码',
            'register_type' => '注册方式',
            'invite_code' => '邀请码',
            'area_id' => '地区',
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
            $account = $this->input('account', '');
            $register_type = $this->input('register_type', '');
            $area_id = $this->input('area_id', '');
            $invite_code = $this->input('invite_code', '');

            if ($register_type == 'email') {
                $result = filter_var($account, FILTER_VALIDATE_EMAIL);
                if (!$result) {
                    return $validator->errors()->add('account', '邮箱不正确');
                }
            } elseif ($register_type == 'mobile') {
                if (!is_numeric($account)) {
                    return $validator->errors()->add('account', '手机号不正确');
                }
            } else {
                return $validator->errors()->add('account', '只能选择邮箱注册或手机注册');
            }

            if (User::where($register_type, $account)->exists()) {
                return $validator->errors()->add('account', '此用户已存在');
            }
            if (!Area::find($area_id)) {
                return $validator->errors()->add('area_id', '找不到这个地区');
            }
            if ($invite_code && !User::where('invite_code', $invite_code)->exists()) {
                return $validator->errors()->add('invite_code', '找不到这个上级');
            }
        });
    }

}
