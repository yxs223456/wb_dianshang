<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-11-24
 * Time: 10:42
 */

namespace app\api\controller;

use app\common\AppException;
use app\common\service\CartService;

class Cart extends Base
{
    protected $beforeActionList = [
        'checkAuth'
    ];

    public function add()
    {
        $goodsId = input("g_id");
        $goodsNum = input("num", 1);
        if (!checkInt($goodsId, false) || !checkInt($goodsNum, false)) {
            AppException::factory(AppException::COM_PARAMS_ERR);
        }

        $user = $this->query["user"];
        $service = new CartService();
        $returnData = $service->add($user, $goodsId, $goodsNum);
        return $this->jsonResponse($returnData);
    }

    public function setNum()
    {
        $cartId = input("c_id");
        $goodsNum = input("num", 1);
        if (!checkInt($cartId, false) || !checkInt($goodsNum, false)) {
            AppException::factory(AppException::COM_PARAMS_ERR);
        }

        $user = $this->query["user"];
        $service = new CartService();
        $returnData = $service->setNum($user, $cartId, $goodsNum);
        return $this->jsonResponse($returnData);
    }

    public function delete()
    {
        $cartId = input("c_id");
        if (!checkInt($cartId, false)) {
            AppException::factory(AppException::COM_PARAMS_ERR);
        }

        $user = $this->query["user"];
        $service = new CartService();
        $returnData = $service->delete($user, $cartId);
        return $this->jsonResponse($returnData);
    }

    public function all()
    {
        $user = $this->query["user"];
        $service = new CartService();
        $returnData = $service->all($user);
        return $this->jsonResponse($returnData);
    }
}