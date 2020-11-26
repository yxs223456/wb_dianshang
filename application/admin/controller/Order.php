<?php

namespace app\admin\controller;

use app\common\enum\DbDataIsDeleteEnum;
use app\common\enum\OrderStatusEnum;
use app\common\model\ExpressCompanyModel;
use app\common\model\GoodsOrderModel;
use think\Db;
use think\exception\PDOException;

class Order extends Common {

    /**
     * 待发货订单
     */
    public function waitDelivery() {

        $requestMap = $this->convertRequestToMap();
        $requestMap["condition"]["order_status"] = OrderStatusEnum::WAIT_DELIVERY;
        $orderModel = new GoodsOrderModel();
        $list = $orderModel->paginateList($requestMap);

        $orderIds = [];
        $orderGoodsInfo = [];
        foreach ($list as $item) {
            $orderIds[] = $item["id"];
        }
        if ($orderIds) {
            $orderGoodsList = Db::name("goods_order_info")
                ->whereIn("o_id", $orderIds)
                ->field("o_id,g_num,g_price,g_name,g_image_url")
                ->select();
            foreach ($orderGoodsList as $item) {
                $orderGoodsInfo[$item["o_id"]][] = $item;
            }
        }
        foreach ($list as $item) {
            $item["goodsList"] = $orderGoodsInfo[$item["id"]];
            $item["pay_date"] = date("m-d H:i:s", $item["pay_time"]);
        }

        $this->assign('list',$list);

        return $this->fetch();
    }

    /**
     * 待收货订单
     */
    public function delivery() {

        $requestMap = $this->convertRequestToMap();
        $requestMap["condition"]["order_status"] = OrderStatusEnum::WAIT_RECEIVE;
        $orderModel = new GoodsOrderModel();
        $list = $orderModel->paginateList($requestMap);

        $orderIds = [];
        $orderGoodsInfo = [];
        $expressIds = [];
        $expressCInfo = [];
        foreach ($list as $item) {
            $orderIds[] = $item["id"];
            $expressIds[] = $item["express_c_id"];
        }
        if ($orderIds) {
            $orderGoodsList = Db::name("goods_order_info")
                ->whereIn("o_id", $orderIds)
                ->field("o_id,g_num,g_price,g_name,g_image_url")
                ->select();
            foreach ($orderGoodsList as $item) {
                $orderGoodsInfo[$item["o_id"]][] = $item;
            }
            $expressCInfo = Db::name("express_company")->whereIn("id", $expressIds)->select();
            $expressCInfo = array_column($expressCInfo, null, "id");
        }
        foreach ($list as $item) {
            $item["goodsList"] = $orderGoodsInfo[$item["id"]];
            $item["pay_date"] = date("m-d H:i:s", $item["pay_time"]);
            $item["express_c_name"] = $expressCInfo[$item["express_c_id"]]["name"];
            $item["delivery_date"] = date("m-d H:i:s", $item["delivery_time"]);
        }

        $this->assign('list',$list);

        return $this->fetch();
    }

    /**
     * 已收货列表
     */
    public function receive() {

        $requestMap = $this->convertRequestToMap();
        $requestMap["condition"]["order_status"] = OrderStatusEnum::RECEIVED;
        $orderModel = new GoodsOrderModel();
        $list = $orderModel->paginateList($requestMap);

        $orderIds = [];
        $orderGoodsInfo = [];
        $expressIds = [];
        $expressCInfo = [];
        foreach ($list as $item) {
            $orderIds[] = $item["id"];
            $expressIds[] = $item["express_c_id"];
        }
        if ($orderIds) {
            $orderGoodsList = Db::name("goods_order_info")
                ->whereIn("o_id", $orderIds)
                ->field("o_id,g_num,g_price,g_name,g_image_url")
                ->select();
            foreach ($orderGoodsList as $item) {
                $orderGoodsInfo[$item["o_id"]][] = $item;
            }
            $expressCInfo = Db::name("express_company")->whereIn("id", $expressIds)->select();
            $expressCInfo = array_column($expressCInfo, null, "id");
        }
        foreach ($list as $item) {
            $item["goodsList"] = $orderGoodsInfo[$item["id"]];
            $item["pay_date"] = date("m-d H:i:s", $item["pay_time"]);
            $item["express_c_name"] = $expressCInfo[$item["express_c_id"]]["name"];
            $item["delivery_date"] = date("m-d H:i:s", $item["delivery_time"]);
            $item["receive_date"] = date("m-d H:i:s", $item["receive_time"]);
        }

        $this->assign('list',$list);

        return $this->fetch();
    }

    /**
     * 编辑订单物流操作页面
     */
    public function editDelivery() {

        $id = input('param.id');

        $orderModel = new GoodsOrderModel();
        $expressCModel = new ExpressCompanyModel();
        $company = $expressCModel->where("is_delete", DbDataIsDeleteEnum::NO)->select();
        $order = $orderModel->findById($id);
        if (!$order) {
            $this->error($orderModel->getError());
        }

        $this->assign('company',$company);
        $this->assign("info", $order);
        return $this->fetch();
    }

    /**
     * 纪录、修改订单物流
     */
    public function editDeliveryPost() {

        $param = input('post.');
        $orderModel = new GoodsOrderModel();
        $order = $orderModel->findById($param["id"]);

        Db::startTrans();
        try{

            if ($order["order_status"] == OrderStatusEnum::WAIT_DELIVERY) {
                // 纪录未收货订单表
                Db::name("tmp_wait_receive_order")->insert([
                    "o_id" => $param["id"],
                    "create_time" => time(),
                    "update_time" => time(),
                ]);
                $order->order_status = OrderStatusEnum::WAIT_RECEIVE;
                $order->delivery_time = time();
            }

            $order->express_c_id = $param["express_c_id"];
            $order->express_code = $param["express_code"];

            $order->save();
            Db::commit();

            $this->success("编辑物流成功",url("waitDelivery"));

        } catch(PDOException $e) {
            $this->error($e->getMessage());
            Db::rollback();
        }

    }
}