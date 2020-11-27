<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-11-23
 * Time: 16:07
 */

namespace app\common\service;

use app\common\AppException;
use app\common\model\CartModel;
use think\Db;

class CartService extends Base
{
    public function add($user, $goodsId, $goodsNum)
    {
        $cartModel = new CartModel();
        $cart = $cartModel->findByUIdAndGId($user["id"], $goodsId);
        if (empty($cart)) {
            $cartModel->save([
                "u_id" => $user["id"],
                "g_id" => $goodsId,
                "g_num" => $goodsNum,
            ]);
        } else {
            $cartModel
                ->where("id", $cart["id"])
                ->inc("g_num", $goodsNum)
                ->update([
                    "update_time" => time(),
                ]);
        }

        return new \stdClass();
    }

    public function setNum($user, $cartId, $goodsNum)
    {
        $cartModel = new CartModel();
        $cart = $cartModel->findById($cartId);
        if (empty($cart) || $cart["u_id"] != $user["id"]) {
            AppException::factory(AppException::COM_INVALID);
        }

        $cart->g_num = $goodsNum;
        $cart->save();

        return new \stdClass();
    }

    public function delete($user, $cartId)
    {
        $cartModel = new CartModel();
        $cart = $cartModel->findById($cartId);
        if ($cart) {
            if ($cart["u_id"] != $user["id"]) {
                AppException::factory(AppException::COM_INVALID);
            }
            $cartModel->deleteById($cartId);
        }

        return new \stdClass();
    }

    public function all($user)
    {
        $cartList = Db::name("cart")->alias("c")
            ->leftJoin("goods g", "c.g_id=g.id")
            ->where("c.u_id", $user["id"])
            ->field("c.id c_id,c.g_id,c.g_num,
            g.goods_name,g.image_url,g.price,g.classify,g.is_show")
            ->select();

        return $cartList;
    }
}