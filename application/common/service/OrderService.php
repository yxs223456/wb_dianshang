<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-11-24
 * Time: 14:43
 */

namespace app\common\service;

use app\common\AppException;
use app\common\enum\IsPayEnum;
use app\common\enum\IsShowEnum;
use app\common\enum\OrderStatusEnum;
use app\common\enum\PaySceneEnum;
use app\common\helper\WeChatPay;
use app\common\model\GoodsOrderModel;
use think\Db;
use think\facade\Request;

class OrderService extends Base
{
    public function create($user, $cartIds, $deliveryName, $deliveryPhone, $deliveryAddress)
    {
        $allCartGoodsInfo = Db::name("cart")->alias("c")
            ->leftJoin("goods g", "c.g_id=g.id")
            ->whereIn("c.id", $cartIds)
            ->field("c.id c_id,c.u_id,c.g_id,c.g_num,
            g.goods_name,g.image_url,g.price,g.is_show")
            ->select();

        if (empty($allCartGoodsInfo)) {
            AppException::factory(AppException::COM_INVALID);
        }

        // 防止已经下架的商品进入订单
        foreach ($allCartGoodsInfo as $key=>$item) {
            if ($item["u_id"] != $user["id"]) {
                AppException::factory(AppException::COM_INVALID);
            }
            if ($item["is_show"] == IsShowEnum::NO) {
                unset($allCartGoodsInfo[$key]);
            }
        }
        if (empty($allCartGoodsInfo)) {
            AppException::factory(AppException::ORDER_GOODS_EMPTY);
        }

        $validCartIds = []; // 商品没有下架的购物车id
        $orderTotalMoney = 0; // 订单总金额
        foreach ($allCartGoodsInfo as $item) {
            $validCartIds[] = $item["c_id"];
            $orderTotalMoney = bcadd($orderTotalMoney, bcmul($item["price"], $item["g_num"], 2), 2);
        }

        $orderNo = uniqid(getRandomString(5));
        $now = time();
        Db::startTrans();
        try {
            // 删除已经生成订单的购物车
            Db::name("cart")->whereIn("id", $validCartIds)->delete();
            // 纪录订单表数据
            $orderInfo = [
                "u_id" => $user["id"],
                "order_no" => $orderNo,
                "order_status" => OrderStatusEnum::WAIT_PAY,
                "goods_money" => $orderTotalMoney,
                "total_money" => $orderTotalMoney,
                "delivery_name" => $deliveryName,
                "delivery_phone" => $deliveryPhone,
                "delivery_address" => $deliveryAddress,
                "create_time" => $now,
                "update_time" => $now,
            ];
            $orderInfo["id"] = Db::name("goods_order")->insertGetId($orderInfo);
            // 纪录订单商品表
            $goodsOrderInfo = [];
            foreach ($allCartGoodsInfo as $item) {
                $goodsOrderInfo[] = [
                    "o_id" => $orderInfo["id"],
                    "g_id" => $item["g_id"],
                    "g_num" => $item["g_num"],
                    "g_price" => $item["price"],
                    "g_name" => $item["goods_name"],
                    "g_image_url" => $item["image_url"],
                    "create_time" => $now,
                    "update_time" => $now,
                ];
            }
            // 纪录未支付订单表
            Db::name("goods_order_info")->insertAll($goodsOrderInfo);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }

        return [
            "o_id" => $orderInfo["id"],
        ];
    }

    public function pay($user, $orderId)
    {
        $orderModel = new GoodsOrderModel();
        $order = $orderModel->findById($orderId);
        if (empty($order)) {
            AppException::factory(AppException::COM_INVALID);
        }

        if ($order != OrderStatusEnum::WAIT_PAY) {
            AppException::factory(AppException::ORDER_NOT_WAIT_PAY);
        }

        $returnData = $this->createWxPayOrderForOrder($order, $user["wc_mp_openid"]);

        return $returnData;
    }

    public function createWxPayOrderForOrder($order, $openid = "")
    {
        $orderTradeNo = uniqid(getRandomString(5));
        $now = time();
        Db::name("wx_pay_order")->insert([
            "u_id" => $order["u_id"],
            "scene" => PaySceneEnum::GOODS_ORDER,
            "scene_id" => $order["id"],
            "out_trade_no" => $orderTradeNo,
            "is_pay" => IsPayEnum::NO,
            "create_time" => $now,
            "update_time" => $now,
        ]);

        $wxPayHelper = new WeChatPay();
        $wxUnifiedOrderParams = [
            "body" => "支付订单",
            "out_trade_no" => $orderTradeNo,
            "total_fee" => $order["total_money"] * 100,
            "ip" => Request::ip(),
            "notify_url" => "",
            "trade_type" => "JSAPI",
            "openid" => $openid,
        ];
        $wxPayOrder = $wxPayHelper->wxUnifiedOrder($wxUnifiedOrderParams);
        return $wxPayOrder;
    }

    public function afterPayCallback($orderId)
    {

    }
}

