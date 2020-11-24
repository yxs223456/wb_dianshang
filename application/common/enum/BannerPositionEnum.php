<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-09-02
 * Time: 10:23
 */

namespace app\common\enum;

/**
 * banner 位置
 */
class BannerPositionEnum
{
    use EnumTrait;

    const MP_HOME = 1;

    protected static $desc = [
        self::MP_HOME => [
            "cn" => "小程序首页",
        ],
    ];
}