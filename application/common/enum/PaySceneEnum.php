<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-09-01
 * Time: 11:26
 */

namespace app\common\enum;

/**
 * 支付场景
 */
class PaySceneEnum
{
    use EnumTrait;

    const GOODS_ORDER = 1;

    protected static $desc = [
        self::GOODS_ORDER => [
            "cn" => "商品订单",
        ],
    ];
}