<?php
/**
 * Created by PhpStorm.
 * User: yangxs
 * Date: 2018/9/18
 * Time: 17:44
 */
namespace app\common;

class AppException extends \Exception
{
    const COM_PARAMS_ERR = [1, "请求参数错误"];
    const COM_FILE_ERR = [2, "上传文件不存在或超过服务器限制"];
    const COM_DATE_ERR = [3, "日期格式错误"];
    const COM_MOBILE_ERR = [4, "手机号格式错误"];
    const COM_ADDRESS_ERR = [5, "地址信息不全"];
    const COM_INVALID = [6, "非法请求"];

    const USER_NOT_LOGIN = [1000, "您还未登录"];
    const USER_NOT_EXISTS = [1001, "用户不存在"];

    const WECHAT_GET_USER_INFO_ERR = [2000, '获取用户微信信息失败'];
    const WECHAT_MINPROGRAM_LOGIN_ILLEGALAESKEY = [2001, 'encodingAesKey 非法'];
    const WECHAT_MINPROGRAM_LOGIN_ILLEGALIV = [2002, '微信凭证获取失败'];
    const WECHAT_MINPROGRAM_LOGIN_ILLEGALBUFFER = [2003, '解密后得到的buffer非法'];
    const WECHAT_MINPROGRAM_LOGIN_DECODEBASE64ERROR = [2004, 'base64解密失败'];
    const WECHAT_MINPROGRAM_APPID_ERROR = [2005, 'appid不一致'];
    const WECHAT_QUERY_FILE = [2006, '请求微信失败'];
    const WECHAT_PAY_ORDER_NOT_EXISTS = [2007, '找不到微信支付信息'];

    const DEMAND_SUBMIT_FREQUENT = [3000, "缓一缓，您刚刚提交过了"];

    const ORDER_GOODS_EMPTY = [4000, "抱歉，商品已下架"];
    const ORDER_NOT_WAIT_PAY = [4001, "抱歉，订单已经超时"];

    public static function factory($errConst)
    {
        $e = new self($errConst[1], $errConst[0]);
        throw $e;
    }
}