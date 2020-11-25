<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-11-25
 * Time: 11:36
 */

namespace app\api\controller;

use app\common\enum\IsPayEnum;
use app\common\enum\PayMethodEnum;
use app\common\enum\PaySceneEnum;
use app\common\helper\WeChatPay;
use app\common\service\OrderService;
use think\Controller;
use think\Db;

class PayCallback extends Controller
{
    public function weChatPay()
    {
        // 接收微信支付回调通知，并验证
        $weChatHelper = new WeChatPay();
        $weChatNotice = $weChatHelper->parseNotice();

        // 支付后续处理
        Db::startTrans();
        try {
            /**
             * 微信支付订单处理
             */
            $wxPayOrder = Db::name("pay_order_wx")
                ->where("out_trade_no", $weChatNotice["out_trade_no"])
                ->lock(true)
                ->find();
            // 判断是否已处理（非常重要）
            if ($wxPayOrder["is_pay"] == IsPayEnum::YES) {
                throw new \Exception("微信支付订单已处理" . json_encode($weChatNotice, JSON_UNESCAPED_UNICODE));
            }
            // 判断订单金额是否对应
            if ($weChatNotice["total_fee"] != bcmul($wxPayOrder["amount"], 100)) {
                throw new \Exception("支付金额不对应:" . json_encode($weChatNotice, JSON_UNESCAPED_UNICODE));
            }
            // 将微信支付订单修改为已支付状态
            Db::name("pay_order_wx")
                ->where("id", $wxPayOrder["id"])
                ->update([
                    "is_pay" => IsPayEnum::YES,
                    "transaction_id" => $weChatNotice["transaction_id"],
                    "notify_data" => json_encode($weChatNotice, JSON_UNESCAPED_UNICODE),
                    "update_time" => time(),
                ]);

            /**
             * 根据支付场景，进行后续处理
             */
            switch ($wxPayOrder["scene"]) {
                case PaySceneEnum::GOODS_ORDER:
                    OrderService::afterPayCallback(
                        $wxPayOrder["scene_id"],
                        strtotime($wxPayOrder["time_end"]),
                        PayMethodEnum::WE_CHAT,
                        $weChatNotice["transaction_id"]
                    );
                    break;
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }

        return <<<XML
<xml>
  <return_code><![CDATA[SUCCESS]]></return_code>
  <return_msg><![CDATA[OK]]></return_msg>
</xml>
XML;
    }
}