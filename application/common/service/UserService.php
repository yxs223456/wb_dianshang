<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-11-23
 * Time: 15:10
 */
namespace app\common\service;

use app\common\AppException;
use app\common\helper\MiniProgram;
use app\common\helper\Redis;
use app\common\model\UserInfoModel;
use app\common\model\UserModel;
use think\Db;

class UserService extends Base
{
    public function mpLogin($sessionKeyId, $encryptedData, $iv)
    {
        //通过sessionKeyId获取sessionKey
        $redis = Redis::factory();
        $sessionKey = getUserSessionKey($sessionKeyId, $redis);

        //从微信获取用户信息
        $mpUserInfo = MiniProgram::getUserInfo($sessionKey, $encryptedData, $iv);
        if (empty($mpUserInfo)) {
            AppException::factory(AppException::WECHAT_GET_USER_INFO_ERR);
        }

        //使用unionid作为用户唯一标识
        $userModel = new UserModel();
        $user = $userModel->findByMpOpenId($mpUserInfo['openId']);
        if (empty($user)) {
            $userId = $this->createUserByMp($mpUserInfo, $redis);
        } else {
            $userId = $user["id"];
            $this->updateUserInfoByMp($userId, $mpUserInfo, $redis);
        }

        $returnData = $this->userFormat($userId, $redis);
        return $returnData;
    }

    public function userInfo($user)
    {
        $redis = Redis::factory();
        $returnData = $this->userFormat($user["id"], $redis);
        return $returnData;
    }

    public function userFormat($userId, $redis = null)
    {
        if ($redis == null) {
            $redis = Redis::factory();
        }
        $user = $this->getUserByUId($userId, $redis);
        $userInfo = $this->getUserInfoByUId($userId, $redis);

        return [
            "u_id" => $userId,
            "token" => $user["token"],
            "nickname" => $userInfo["nickname"],
            "portrait" => $userInfo["portrait"],
        ];
    }

    private function updateUserInfoByMp($userId, $mpUserInfo, $redis)
    {
        $userInfo = $this->getUserInfoByUId($userId, $redis);
        $isUpdate = false;
        if ($userInfo["nickname"] != $mpUserInfo["nickname"] ||
            $userInfo["portrait"] != $mpUserInfo["avatarUrl"]) {
            $isUpdate = true;
        }
        if ($isUpdate) {
            $userInfoModel = new UserInfoModel();
            $userInfoUpdateData = [
                "nickname" => $mpUserInfo["nickname"],
                "portrait" => $mpUserInfo["avatarUrl"],
            ];
            $userInfoModel->save($userInfoUpdateData, ["id"=>$userInfo["id"]]);
            $userInfo["nickname"] = $mpUserInfo["nickname"];
            $userInfo["portrait"] = $mpUserInfo["avatarUrl"];
            cacheUserInfoById($userInfo, $redis);
        }
    }

    private function createUserByMp($mpUserInfo, $redis)
    {
        $token = getRandomString();
        $userNum = createUserNumber(6);
        $now = time();
        Db::startTrans();
        try {
            // 创建user表数据
            $user = [
                "wc_unionid" => $mpUserInfo["unionId"],
                "wc_mp_openid" => $mpUserInfo["openId"],
                "user_number" => $userNum,
                "token" => $token,
                "create_time" => $now,
                "update_time" => $now,
            ];
            $user["id"] = Db::name("user")->insertGetId($user);

            $userInfo = [
                "u_id" => $user["id"],
                "nickname" => $mpUserInfo["nickname"],
                "portrait" => $mpUserInfo["avatarUrl"],
                "create_time" => $now,
                "update_time" => $now,
            ];
            $userInfo["id"] = Db::name("user_info")->insertGetId($userInfo);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }

        cacheUserByToken($user, $redis);
        cacheUserById($user, $redis);
        cacheUserInfoById($userInfo, $redis);

        return $user["id"];
    }

    public function getUserByUId($userId, $redis)
    {
        $user = getUserById($userId, $redis);
        if (empty($user)) {
            $userModel = new UserModel();
            $user = $userModel->findById($userId);
            if (empty($user)) {
                AppException::factory(AppException::USER_NOT_EXISTS);
            }
            $user = $user->toArray();
            cacheUserByToken($user, $redis);
        }

        return $user;
    }

    public function getUserInfoByUId($userId, $redis)
    {
        $userInfo = getUserInfoById($userId, $redis);
        if (empty($userInfo)) {
            $userInfoModel = new UserInfoModel();
            $userInfo = $userInfoModel->findByUId($userId)->toArray();
            cacheUserInfoById($userInfo, $redis);
        }

        return $userInfo;
    }
}