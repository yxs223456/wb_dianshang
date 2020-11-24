<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-09-24
 * Time: 15:39
 */
namespace app\common\model;

class UserInfoModel extends Base
{
    protected $table = "user_info";

    public function findByUId($userId)
    {
        return $this->where("u_id", $userId)->find();
    }
}