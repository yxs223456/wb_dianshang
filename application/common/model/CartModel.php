<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-09-24
 * Time: 15:39
 */
namespace app\common\model;

class CartModel extends Base
{
    protected $table = "cart";

    public function findByUIdAndGId($userId, $goodsId)
    {
        return $this->where("u_id", $userId)->where("g_id", $goodsId)->find();
    }

    public function deleteByUIdAndGId($userId, $goodsId)
    {
        return $this->where("u_id", $userId)->where("g_id", $goodsId)->delete();
    }
}