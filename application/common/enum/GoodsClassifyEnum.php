<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-09-01
 * Time: 11:26
 */

namespace app\common\enum;

/**
 * 商品分类
 */
class GoodsClassifyEnum
{
    use EnumTrait;

    const SOFTWARE = 1;
    const HARDWARE = 2;

    protected static $desc = [
        self::SOFTWARE => [
            "cn" => "软件",
        ],
        self::HARDWARE => [
            "cn" => "硬件",
        ],
    ];
}