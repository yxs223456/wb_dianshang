<?php

namespace app\common\helper;

use think\facade\Env;
use think\Facade\Log;

include_once Env::get('root_path') . 'extend/wxpay/WxPay.Data.php';
include_once Env::get('root_path') . 'extend/wxpay/WxPay.Api.php';

class WeChatPay
{
    public function getWxMchConfigByAppId()
    {
        $config = config('account.we_chat_mch');
        return $config;
    }

    // 向微信发送退款请求
    public function wxRefund(array $wxRefundParams)
    {
        $wxRefund = new \WxPayRefund();
        $wxRefund->SetTransaction_id($wxRefundParams['transaction_id']);
        $wxRefund->SetOut_refund_no($wxRefundParams['out_refund_no']);
        $wxRefund->SetTotal_fee($wxRefundParams['total_fee']);
        $wxRefund->SetRefund_fee($wxRefundParams['refund_fee']);
        $wxRefund->SetRefund_desc($wxRefundParams['refund_desc']);
        $wxRefund->SetRefund_account('REFUND_SOURCE_RECHARGE_FUNDS');

        $mchConfig = $this->getWxMchConfigByAppId();
        new \WxPayConfig($mchConfig);
        $result = \WxPayApi::refund($wxRefund);
        return $result;
    }

    //向微信查询退款进度
    public function wxRefundQuery(array $wxRefundQueryParams)
    {
        $wxRefundQuery = new \WxPayRefundQuery();
        $wxRefundQuery->SetOut_refund_no($wxRefundQueryParams['out_refund_no']);

        $mchConfig = $this->getWxMchConfigByAppId();
        new \WxPayConfig($mchConfig);
        $result = \WxPayApi::refundQuery($wxRefundQuery);
        return $result;
    }

    //微信统一下单
    public function wxUnifiedOrder(array $wxUnifiedOrderParams)
    {
        $wxPayUnifiedOrder = new \WxPayUnifiedOrder();
        $wxPayUnifiedOrder->SetBody($wxUnifiedOrderParams['body']);
        $wxPayUnifiedOrder->SetOut_trade_no($wxUnifiedOrderParams['out_trade_no']);
        $wxPayUnifiedOrder->SetTotal_fee($wxUnifiedOrderParams['total_fee']);
        $wxPayUnifiedOrder->SetSpbill_create_ip($wxUnifiedOrderParams['ip']);
        $wxPayUnifiedOrder->SetNotify_url($wxUnifiedOrderParams['notify_url']);
        $wxPayUnifiedOrder->SetTrade_type($wxUnifiedOrderParams['trade_type']);
        if ($wxUnifiedOrderParams['trade_type'] == 'JSAPI') {
            $wxPayUnifiedOrder->SetOpenid($wxUnifiedOrderParams['openid']);
        }
        $wxPayUnifiedOrder->SetLimit_pay('no_credit');
        $mchConfig = $this->getWxMchConfigByAppId();
        new \WxPayConfig($mchConfig);
        $res = \WxPayApi::unifiedOrder($wxPayUnifiedOrder);
        return $res;
    }

    //发放普通红包
    public function sendRedPack(array $requestInfo)
    {

        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';

        $appId = $requestInfo['wxappid'];
        $mchConfig = config('web.mch.' . $appId);

        $requestInfo['nonce_str'] = getRandomString();
        $requestInfo['mch_id'] = $mchConfig['mchid'];
        $requestInfo['client_ip'] = $_SERVER['SERVER_ADDR'];
        $requestInfo['sign'] = $this->createSign($requestInfo, $mchConfig['key']);
        $xml = $this->arrayToXml($requestInfo);

        $result = $this->wxPost($xml, $url, 10, true, $mchConfig['apiclient_cert'], $mchConfig['apiclient_key']);

        if ($result == false) {
            return false;
        } else {
            return $this->xmlToArray($result);
        }

    }

    public function sendRedPackQuery($requestInfo)
    {

        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gethbinfo';

        $appId = $requestInfo['appid'];
        $mchConfig = config('web.mch.' . $appId);

        $requestInfo['nonce_str'] = getRandomString();
        $requestInfo['mch_id'] = $mchConfig['mchid'];
        $requestInfo['bill_type'] = 'MCHT';
        $requestInfo['sign'] = $this->createSign($requestInfo, $mchConfig['key']);
        $xml = $this->arrayToXml($requestInfo);

        $result = $this->wxPost($xml, $url, 10, true, $mchConfig['apiclient_cert'], $mchConfig['apiclient_key']);

        if ($result == false) {
            return false;
        } else {
            return $this->xmlToArray($result);
        }

    }

    protected function wxPost($xml, $url, $second = 10, $useCert = false, $certPem = '', $keyPem = '')
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (true == $useCert) {
            $cert = $certPem;
            $key = $keyPem;
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, $cert);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, $key);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $data = curl_exec($ch);

        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            Log::write('微信请求失败，失败原因：' . $error, 'error');
            return false;
        }

    }

    public function wxPayNotify($notifyXml)
    {
        $wxPayResult = new \WxPayResults();

        $notify = $wxPayResult::Init($notifyXml);
        if ($notify['return_code'] != 'SUCCESS') {
            $err['return_code'] = 'FAIL';
            $err['return_msg'] = $notify['return_msg'];
            return $this->arrayToXml($err);
        }
        return $notify;
    }

    public function arrayToXml($arr)
    {
        if (!is_array($arr) || count($arr) <= 0) {
            return '数组异常';
        }
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } elseif ($key == 'Image') {
                $xml .= "<" . $key . "><MediaId><![CDATA[" . $val . "]]></MediaId></" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    public function xmlToArray($xml)
    {
        libxml_disable_entity_loader(true);

        $data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        return json_decode(json_encode($data), true);
    }

    public function filterPayReturn($wxUnifiedOrder)
    {
        $data = [
            'appId' => $wxUnifiedOrder['appid'],
            'timeStamp' => (string)time(),
            'nonceStr' => getRandomString(16),
            'package' => 'prepay_id=' . $wxUnifiedOrder['prepay_id'],
            'signType' => 'MD5',
        ];
        $key = config('web.mch.' . $wxUnifiedOrder['appid'] . '.key');
        $data['paySign'] = $this->createSign($data, $key);
        return $data;
    }

    protected function createSign($arr, $mchkey)
    {
        //签名步骤一：按字典序排序参数
        ksort($arr);
        $string = $this->ToUrlParams($arr);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $mchkey;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    protected function ToUrlParams($arr)
    {
        $buff = "";
        foreach ($arr as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
}

