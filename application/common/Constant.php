<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-02-26
 * Time: 17:21
 */

namespace app\common;

class Constant
{
    const COMMON_ADMIN_GROUP_ID = 6;

    // 订单超时时间 24小时
    const ORDER_TIMEOUT_TIME = 86400;

    // 订单自动收货时间 10天
    const ORDER_RECEIVED_TIME = 10 * 86400;
}