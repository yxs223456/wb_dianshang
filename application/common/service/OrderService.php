<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-11-24
 * Time: 14:43
 */

namespace app\common\service;

use app\common\AppException;
use app\common\enum\IsShowEnum;
use think\Db;

class OrderService extends Base
{
    public function create($user, $cartIds)
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

        Db::startTrans();
        try {
            // 删除已经生成订单的购物车
            Db::name("cart")->whereIn("id", $validCartIds)->delete();
            // 纪录订单表数据


            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }

    }
}

