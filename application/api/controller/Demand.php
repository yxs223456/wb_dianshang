<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-11-23
 * Time: 18:04
 */

namespace app\api\controller;

use app\common\AppException;
use app\common\service\DemandService;

class Demand extends Base
{
    protected $beforeActionList = [
        'checkAuth'
    ];

    public function submit()
    {
        $category = input("category");
        $description = input("description");
        $phone = input("phone");

        if (empty($description) || empty($phone)) {
            AppException::factory(AppException::COM_PARAMS_ERR);
        }

        $user = $this->query["user"];
        $service = new DemandService();
        $returnData = $service->submit($user, $category, $description, $phone);

        return $this->jsonResponse($returnData);
    }
}