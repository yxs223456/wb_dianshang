<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-06-17
 * Time: 17:56
 */

namespace app\common\enum;

/**
 * 订单状态
 */
class OrderStatusEnum
{
    use EnumTrait;

    const TIMEOUT = -2;
    const CANCEL = -1;
    const WAIT_PAY = 0;
    const WAIT_DELIVERY = 1;
    const WAIT_RECEIVE = 2;
    const RECEIVED = 3;

    protected static $desc = [
        self::TIMEOUT => [
            "cn" => "超时",
        ],
        self::CANCEL => [
            "cn" => "取消",
        ],
        self::WAIT_PAY => [
            "cn" => "待支付",
        ],
        self::WAIT_DELIVERY => [
            "cn" => "待发货",
        ],
        self::WAIT_RECEIVE => [
            "cn" => "待收货",
        ],
        self::RECEIVED => [
            "cn" => "确认收货",
        ],

    ];
}