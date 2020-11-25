<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-11-24
 * Time: 14:42
 */

namespace app\api\controller;

use app\common\AppException;
use app\common\service\OrderService;

class Order extends Base
{
    protected $beforeActionList = [
        'checkAuth'
    ];

    public function create()
    {
        $cartIds = input("c_ids", []);
        $deliveryName = input("delivery_name", "");
        $deliveryPhone = input("delivery_phone", "");
        $deliveryAddress = input("delivery_address", "");

        if (empty($deliveryName) || empty($deliveryPhone) || empty($deliveryAddress) ||
            !is_array($cartIds) || empty($cartIds)) {
            AppException::factory(AppException::COM_PARAMS_ERR);
        }

        foreach ($cartIds as $cartId) {
            if (!checkInt($cartId, false)) {
                AppException::factory(AppException::COM_PARAMS_ERR);
            }
        }

        $user = $this->query["user"];
        $service = new OrderService();
        $returnData = $service->create($user, $cartIds, $deliveryName, $deliveryPhone, $deliveryAddress);
        return $this->jsonResponse($returnData);
    }

    public function pay()
    {
        $orderId = input("o_id");
        if (!checkInt($orderId, false)) {
            AppException::factory(AppException::COM_PARAMS_ERR);
        }

        $user = $this->query["user"];
        $service = new OrderService();
        $returnData = $service->pay($user, $orderId);
        return $this->jsonResponse($returnData);
    }

    public function list()
    {
        $pageNum = input("page_num", 1);
        $pageSize = input("page_size", 10);
        $orderStatus = input("order_status", 0);

        if (!checkInt($pageNum, false) || !checkInt($pageSize, false)) {
            AppException::factory(AppException::COM_PARAMS_ERR);
        }
        if (!checkInt($orderStatus, true, true)) {
            AppException::factory(AppException::COM_PARAMS_ERR);
        }

        $user = $this->query["user"];
        $service = new OrderService();
        $returnData = $service->list($user, $orderStatus, $pageNum, $pageSize);
        return $this->jsonResponse($returnData);
    }
}