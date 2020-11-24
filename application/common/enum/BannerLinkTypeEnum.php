<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-09-02
 * Time: 10:23
 */

namespace app\common\enum;

/**
 * banner 跳转类型
 */
class BannerLinkTypeEnum
{
    use EnumTrait;

    const NONE = 0;
    const MP_LOCAL = 1;
    const H5 = 2;
    const APP = 3;

    protected static $desc = [
        self::NONE => [
            "cn" => "不跳转",
        ],
        self::MP_LOCAL => [
            "cn" => "小程序内部跳转",
        ],
        self::H5 => [
            "cn" => "web页面",
        ],
        self::APP => [
            "cn" => "app内部跳转",
        ],
    ];
}