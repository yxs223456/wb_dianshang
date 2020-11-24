<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-11-23
 * Time: 17:15
 */

namespace app\api\controller;

use app\common\AppException;
use app\common\service\GoodsService;

class Goods extends Base
{
    protected $beforeActionList = [

    ];

    public function goodsList()
    {
        $pageNum = input("page_num", 1);
        $pageSize = input("page_size", 10);
        $classify = input("classify", 0);

        if (!checkInt($pageNum, false) || !checkInt($pageSize, false)) {
            AppException::factory(AppException::COM_PARAMS_ERR);
        }
        if (!checkInt($classify, false)) {
            AppException::factory(AppException::COM_PARAMS_ERR);
        }

        $service = new GoodsService();
        $returnData = $service->goodsList($classify, $pageNum, $pageSize);
        return $this->jsonResponse($returnData);
    }

    public function goodsInfo()
    {
        $goodsId = input("g_id");

        if (!checkInt($goodsId, false)) {
            AppException::factory(AppException::COM_PARAMS_ERR);
        }

        $service = new GoodsService();
        $returnData = $service->goodsInfo($goodsId);
        return $this->jsonResponse($returnData);
    }
}