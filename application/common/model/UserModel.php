<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-09-24
 * Time: 15:39
 */
namespace app\common\model;

class UserModel extends Base
{
    protected $table = "user";

    public function findByMpOpenId($openId)
    {
        return $this->where("wc_mp_openid", $openId)->find();
    }
}