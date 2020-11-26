<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-06-24
 * Time: 11:19
 */

namespace app\command;

trait CommandTrait
{
    private $maxAllowTime = 600;            //PHP脚本单次运行时间上限
    private $maxAllowMemory = 104857600;    //分配给当前 PHP 脚本的最大允许内存量 100M
}