<?php


namespace App\Notifications\VerificationCode;


use App\Models\Setting\Setting;
use App\Models\User\User;
use App\Models\User\VerificationCode;
use App\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;

class BaseCode extends BaseNotification
{

    const TYPE_REGISTER_CODE            = 1;
    const TYPE_FORGOT_PASSWORD          = 2;
    const TYPE_CHANGE_PAY_PASSWORD_CODE = 3;
    const TYPE_DRAW_CODE                = 4;
    const TYPE_LOGIN_CODE               = 5;

    //短信类型
    const CODE_TYPES = [
        self::TYPE_REGISTER_CODE            => RegisterCode::class,
        self::TYPE_FORGOT_PASSWORD          => ForgotPasswordCode::class,
        self::TYPE_CHANGE_PAY_PASSWORD_CODE => ChangePayPasswordCode::class,
        self::TYPE_DRAW_CODE                => DrawCode::class,
        self::TYPE_LOGIN_CODE               => LoginCode::class,
    ];

    /**
     * @var string 通知类型(验证码场景)
     */
    protected $type = '';

    protected $custom = [
        'type' => '',
        'code' => '',
    ];

    public function __construct()
    {
        $this->initCode();
    }

    /**初始化验证码和发送场景
     *
     */
    public function initCode()
    {
        $this->custom['type'] = $this->type;
        if (!$this->custom['code']) {
            $this->custom['code'] = self::getCode();
        }
    }


    /**
     * @param User $notifiable
     * @return MailMessage
     * @throws \Exception
     */
    public function toMail($notifiable)
    {
        VerificationCode::remember($notifiable->email, static::class, $this->custom['code']);
        return (new MailMessage)->subject('verification code')->view('email.code', $this->custom);
    }

    /**
     * @param User $notifiable
     * @return array|void
     * @throws \Exception
     */
    public function toShortcode($notifiable)
    {
        
        VerificationCode::remember($notifiable['email'], static::class, $this->custom['code']);

        $template = Setting::getValueByKey('sms_verification_code_template', '5f115451efb9a3723c6773c8');
        return [
            'type'   => $template,
            'custom' => $this->custom,
        ];
    }

    public function toDatabase($notifiable)
    {
        $array = parent::toArray($notifiable);
        return array_merge($array, [
            'code' => 1111,
        ]);
    }

    /**获取一个验证码
     *
     * @param int $length
     *
     * @return int
     */
    public static function getCode($length = 4)
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= rand(0, 9);
        }
        return $code;
    }

    /**根据场景获取验证码通知
     * @param $type
     * @return mixed
     */
    public static function getCodeNotifyByType($type)
    {
        $class = self::CODE_TYPES[$type];
        return new $class();
    }

}
