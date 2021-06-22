<?php


namespace App\Utils;


class BC
{
    public static function compute($num1, $symbol, $num2, $decimals = null)
    {
        bcscale($decimals ?? DECIMAL_SCALE);
        $num1 = self::sctonum($num1, $decimals);
        $num2 = self::sctonum($num2, $decimals);

        switch ($symbol) {
            case '+':
                return bcadd($num1, $num2);
                break;
            case '-':
                return bcsub($num1, $num2);
                break;
            case '*':
                return bcmul($num1, $num2);
                break;
            case '/':
                if ($num2 <= 0) {
                    return 0;
                }
                return bcdiv($num1, $num2);
                break;
            case '>':
                return bccomp($num1, $num2) > 0;
                break;
            case '<':
                return bccomp($num1, $num2) < 0;
                break;
            case '>=':
                return bccomp($num1, $num2) >= 0;
                break;
            case '<=':
                return bccomp($num1, $num2) <= 0;
                break;
            case '=':
                // no break;
            case '==':
                return bccomp($num1, $num2) == 0;
                break;
            case '<>':
                // no break;
            case '!=':
                return bccomp($num1, $num2) != 0;
                break;
            case '^':
                return bcpow($num1, $num2);
                break;
        }
    }

    /**
     * 科学计数法转字符串
     *
     * @param float $num 数值
     * @param integer $decimals
     *
     * @return string
     */
    public static function sctonum($num, $decimals = 8)
    {
        if (false !== stripos($num, "e")) {
            $a = explode("e", strtolower($num));
            return bcmul(strval($a[0]), bcpow('10', $a[1], $decimals), $decimals);
        } else {
            return strval($num);
        }
    }
}
