<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-11-23
 * Time: 16:05
 */

namespace app\api\controller;

use app\common\service\BannerService;

class Banner extends Base
{
    protected $beforeActionList = [

    ];

    public function homeBanner()
    {
        $service = new BannerService();
        $returnData = $service->homeBanner();
        return $this->jsonResponse($returnData);
    }
}