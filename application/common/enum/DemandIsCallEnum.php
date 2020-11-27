<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-09-01
 * Time: 11:26
 */

namespace app\common\enum;

/**
 * 定制需求是否处理
 */
class DemandIsCallEnum
{
    use EnumTrait;

    const NO = 0;
    const YES = 1;

    protected static $desc = [
        self::NO => [
            "cn" => "未处理",
        ],
        self::YES => [
            "cn" => "已处理",
        ],
    ];
}