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
use app\common\helper\KuaiDi100;
use app\common\helper\Redis;
use app\common\helper\WeChatPay;
use app\common\model\GoodsOrderModel;
use think\Db;
use think\facade\Request;

class OrderService extends Base
{
    /**
     * 创建订单
     * @param $user
     * @param $cartIds
     * @param $deliveryName
     * @param $deliveryPhone
     * @param $deliveryAddress
     * @return array
     * @throws AppException
     * @throws \Throwable
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
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

    /**
     * 支付订单
     * @param $user
     * @param $orderId
     * @return array
     * @throws AppException
     */
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

    /**
     * 发起小程序支付
     * @param $order
     * @param string $openid
     * @return array
     */
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

    /**
     * 支付回调 外层需开启数据库事务
     * @param $orderId
     * @param $payTime
     * @param $payType
     * @param $payOrderNo
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function afterPayCallback($orderId, $payTime, $payType, $payOrderNo)
    {
        $order = Db::name("goods_order")->where("id", $orderId)->find();

        if ($order["order_status"] == OrderStatusEnum::WAIT_PAY ||
            $order["order_status"] == OrderStatusEnum::CANCEL ||
            $order["order_status"] == OrderStatusEnum::TIMEOUT) {
            Db::name("goods_order")->where("id", $orderId)->update([
                "order_status" => OrderStatusEnum::WAIT_DELIVERY,
                "pay_time" => $payTime,
                "pay_type" => $payType,
                "pay_order_no" => $payOrderNo,
                "update_time" => time(),
            ]);
        }
    }

    public function list($user, $orderStatus, $pageNum, $pageSize)
    {
        $returnData["list"] = [];
        $list = Db::name("goods_order")
            ->where("u_id", $user["id"])
            ->where("order_status", $orderStatus)
            ->field("id o_id,order_no,order_status,total_money,create_time")
            ->order("id", "desc")
            ->limit(($pageNum - 1) * $pageSize, $pageSize)
            ->select();

        if (!empty($list)) {
            $orderList = array_column($list, null, "o_id");
            $orderIds = array_column($list, "o_id");
            $orderGoodsInfo = Db::name("goods_order_info")
                ->whereIn("o_id", $orderIds)
                ->field("o_id,g_id,g_num,g_name,g_image_url")
                ->select();
            foreach ($orderGoodsInfo as $item) {
                $orderList[$item["o_id"]]["goods"][] = [
                    "g_id" => $item["g_id"],
                    "g_num" => $item["g_num"],
                    "g_name" => $item["g_name"],
                    "g_image_url" => $item["g_image_url"],
                ];
            }
            foreach ($orderList as $key => $item) {
                $orderList[$key]["create_time"] = date("m-d H:i:s", $item["create_time"]);
            }
            $returnData["list"] = array_values($orderList);
        }

        return $returnData;
    }

    public function info($user, $orderId)
    {
        $order = Db::name("goods_order")->alias("go")
            ->leftJoin("express_company ec", "go.express_c_id=ec.id")
            ->where("go.id", $orderId)
            ->field("go.*,ec.name,ec.kd_100_code")
            ->find();
        if (empty($order) || $order["u_id"] != $user["id"]) {
            AppException::factory(AppException::COM_INVALID);
        }

        $orderGoods = Db::name("goods_order_info")
            ->where("o_id", $orderId)
            ->field("g_id,g_num,g_name,g_image_url,is_comment")
            ->select();

        $expressInfo = $this->getExpressInfoByOrder($order);

    }

    public function getExpressInfoByOrder($order)
    {
        $expressInfo = [];
        if ($order["status"] >= OrderStatusEnum::WAIT_RECEIVE) {
            $redis = Redis::factory();
            $expressInfo = getOrderExpress($order["id"], $redis);
            if (empty($expressInfo)) {
                $kd100Helper = new KuaiDi100();
                $expressInfo = $kd100Helper->query($order["kd_100_code"], $order["express_code"]);
                $cacheTtl = $order["status"] == OrderStatusEnum::WAIT_RECEIVE ? 300 : 86400;
                cacheOrderExpress($order["id"], $expressInfo, $cacheTtl, $redis);
            }
        }
        return $expressInfo;
    }
}

