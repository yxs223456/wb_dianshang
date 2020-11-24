<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-11-23
 * Time: 18:10
 */

namespace app\common\service;

use app\common\AppException;
use app\common\enum\DemandIsCallEnum;
use app\common\helper\Redis;
use app\common\model\DemandModel;

class DemandService extends Base
{
    public function submit($user, $category, $description, $phone)
    {
        $redis = Redis::factory();
        $sign = getDemandSign($user["id"], $redis);
        if ($sign) {
            AppException::factory(AppException::DEMAND_SUBMIT_FREQUENT);
        }

        addDemandSign($user["id"], $redis);
        $demandModel = new DemandModel();
        $demandModel->save([
            "category" => $category,
            "description" => $description,
            "phone" => $phone,
            "u_id" => $user["id"],
            "is_call" => DemandIsCallEnum::NO,
        ]);

        return new \stdClass();
    }
}