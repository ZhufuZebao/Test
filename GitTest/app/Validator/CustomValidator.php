<?php
/**
 * バリデーターのカスタマイズクラス
 *
 * @author  Miyamoto
 */

namespace App\Validator;

class CustomValidator extends \Illuminate\Validation\Validator
{
    /**
     * 郵便番号のバリデート
     *
     * @param   $attribute
     * @param   $value
     * @param   $parameters
     * @return  boolean|unknown
     */
    public function validateZipcode($attribute, $value, $parameters)
    {
        if ($value == '' || strlen($value) == 0) {
            return true;
        }
        return preg_match('/^\d{3}-\d{4}$/', $value);
    }

    /**
     * 電話番号のバリデート
     *
     * @param   $attribute
     * @param   $value
     * @param   $parameters
     * @return  boolean|unknown
     */
    public function validateTelno($attribute, $value, $parameters)
    {
        if ($value == '' || strlen($value) == 0) {
            return true;
        }
        if (strlen($value) > 13) {
            return false;
        }
        return preg_match('/^[0-9]{2,5}-[0-9]{2,5}-[0-9]{3,4}$/', $value);
    }

    /**
     * 携帯番号のバリデート
     *
     * @param   $attribute
     * @param   $value
     * @param   $parameters
     * @return  boolean|unknown
     */
    public function validateMobileno($attribute, $value, $parameters)
    {
        if ($value == '' || strlen($value) == 0) {
            return true;
        }
        return preg_match('/^\d{3}-\d{4}-\d{4}$/', $value);
    }
/*
    public function validateDate($attribute, $value)
    {
        if ($value == '' || strlen($value) == 0) {
            return true;
        }

        if (date('Y/m/d', strtotime('2018/04/31')) == $value) {
            return true;
        }
        return false;
    }
*/
}
