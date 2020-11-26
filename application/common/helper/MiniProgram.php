<?php
/**
 * Created by PhpStorm.
 * User: yangxs
 * Date: 2018/10/16
 * Time: 15:41
 */

namespace app\common\helper;

use think\facade\Log;

/** 微信小程序相关封装
 * Class MiniProgram
 * @package app\common\helper
 */
class MiniProgram
{
    /**
     * 获取用户微信信息
     * @param $sessionKey
     * @param $encryptedData
     * @param $iv
     * @return array
     */
    public static function getUserInfo($sessionKey, $encryptedData, $iv)
    {
        $mcrypt_decrypt = self::getWxInfoToArray($sessionKey, $encryptedData, $iv);
        if ($mcrypt_decrypt == []) {
            return [];
        }
        return [
            "unionId" => $mcrypt_decrypt["unionId"] ?? null,
            "openId" => $mcrypt_decrypt["openId"] ?? null,
            "nickname" => $mcrypt_decrypt["nickName"]??"",
            "province" => $mcrypt_decrypt["province"]??"",
            "country" => $mcrypt_decrypt["country"]??"",
            "avatarUrl" => $mcrypt_decrypt["avatarUrl"]??"",
            "gender" => $mcrypt_decrypt["gender"]??0,
            "city" => $mcrypt_decrypt["city"]??"",
            "sessionKey" => $sessionKey,
        ];
    }

    /**
     * 获取用户微信绑定手机号
     * @param $sessionKey
     * @param $encryptedData
     * @param $iv
     * @return string
     */
    public static function getUserPhone($sessionKey, $encryptedData, $iv){
        $mcrypt_decrypt = self::getWxInfoToArray($encryptedData, $sessionKey, $iv);
        return $mcrypt_decrypt["phoneNumber"]??"";
    }

    /**
     * 获取 code2Session
     * @param $appId
     * @param $appSecret
     * @param $code
     * @return bool|mixed
     */
    public static function code2Session($appId, $appSecret, $code)
    {
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $appId . '&secret=' . $appSecret . '&js_code=' . $code . '&grant_type=authorization_code';
        $code2Session = curl($url);
        $code2Session = json_decode($code2Session, true);
        if (empty($code2Session) || !isset($code2Session['session_key'])) {
            Log::write(json_encode($code2Session, JSON_UNESCAPED_UNICODE), "error");
            return [];
        }
        return $code2Session;
    }

    private static function getWxInfoToArray($sessionKey, $encryptedData, $iv)
    {
        $aesKey = base64_decode($sessionKey);

        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encryptedData);

        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj = json_decode($result, true);

        if (empty($dataObj)) {
            return [];
        }

        return $dataObj;
    }
}