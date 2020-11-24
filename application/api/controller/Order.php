<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-11-24
 * Time: 14:42
 */

namespace app\api\controller;

use app\common\AppException;

class Order extends Base
{
    protected $beforeActionList = [
        'checkAuth'
    ];
}