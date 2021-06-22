<?php


namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
{
    /**
     * 是否过滤掉null的字段
     *
     * @var bool
     */
    protected $unset_null_field = true;

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     *
     * @return void
     *
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->all();
        if (request()->isXmlHttpRequest()) {
            throw new HttpResponseException(json_response_error($error[0]));
        }
        abort(500, $error[0]);
    }

    /**过滤是null的字段
     *
     * @return array
     */
    public function validationData()
    {
        $data = parent::validationData();
        if (!$this->unset_null_field) {
            return $data;
        }

        $data = array_filter($data, function ($item) {
            return !is_null($item);
        });
        return $data;
    }
}
