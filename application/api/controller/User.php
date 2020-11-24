<?php
/**
 * Created by PhpStorm.
 * User: yangxs
 * Date: 2018/10/16
 * Time: 15:10
 */
namespace app\api\controller;

use app\admin\service\UserBalanceLogs;
use app\common\AppException as AE;
use app\common\enum\UserStatusEnum;
use app\common\helper\MiniProgram;
use app\common\helper\RabbitMQ;
use app\common\helper\Redis;
use app\common\service\User as CSUser;
use app\common\model\Users as UserModel;
use app\common\service\UserService;
use app\constants\Constants;
use think\Db;

class User extends Base
{
    protected $beforeActionList = [
        'checkAuth' => [
            'except' => 'mpLogin,getSessionKeyId',
        ],
    ];

    //更新sessionKey
    public function getSessionKeyId()
    {
        $code = input('code');
        if (empty($code)) {
            throw AE::factory(AE::COM_PARAMS_ERR);
        }

        //小程序配置
        $mpConfig = config('account.weixin.mp');
        $appId = $mpConfig['app_id'];
        $appSecret = $mpConfig['app_secret'];

        //从微信获取sessionKey
        $code2Session = MiniProgram::code2Session($appId, $appSecret, $code);
        $sessionKey = $code2Session['session_key'];

        //使用唯一sessionKeyId缓存sessionKey
        $sessionKeyId = uniqid() . getRandomString(10);
        $redis = Redis::factory();
        setUserSessionKey($sessionKeyId, $sessionKey, $redis);

        $returnData = [
            'session_key_id' => $sessionKeyId,
        ];
        return $this->jsonResponse($returnData);
    }

    //小程序登录
    public function mpLogin()
    {
        $encryptedData = input('encrypted_data');
        $iv = input('iv');
        $sessionKeyId = input('session_key_id');

        if (empty($sessionKeyId) || empty($encryptedData) || empty($iv)) {
            throw AE::factory(AE::COM_PARAMS_ERR);
        }

        $service = new UserService();
        $returnData = $service->mpLogin($sessionKeyId, $encryptedData, $iv);
        return $this->jsonResponse($returnData);
    }

    //用户信息
    public function userInfo()
    {
        $user = $this->query['user'];
        $service = new UserService();
        $returnData = $service->userInfo($user);

        return $this->jsonResponse($returnData);
    }


}