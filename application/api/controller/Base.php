<?php
/**
 * Created by PhpStorm.
 * User: yangxs
 * Date: 2018/9/18
 * Time: 16:24
 */

namespace app\api\controller;

use app\common\AppException;
use app\common\helper\Redis;
use app\common\model\UserModel;
use think\Controller;

class Base extends Controller
{
    protected $beforeActionList = [
        'checkAuth'
    ];

    public $query = [
        "is_login" => false,
        'user' => [],
    ];

    protected function initialize()
    {
        $host = $_SERVER['HTTP_ORIGIN'] ?? '';
        header("Access-Control-Allow-Origin:$host");
        header('Access-Control-Allow-Credentials: true');
        header("Access-Control-Allow-Headers:token");

        parent::initialize();
        static::checkToken();
    }

    protected function jsonResponse($data, $msg = "success")
    {
        $rs = [
            'code' => 0,
            'msg' => $msg,
            'data' => $data,
        ];
        return json($rs);
    }

    protected function checkToken()
    {
        $token = $this->request->header('token');
        if (empty($token)) {
            $isLogin = false;
            $user = [];
        } else {
            $redis = Redis::factory();
            $cacheUser = getUserByToken($token, $redis);
            if (empty($cacheUser['userId'])) {
                $model = new UserModel();
                $userModel = $model->where("token", $token)->find();
                if (!$userModel) {
                    $isLogin = false;
                    $user = [];
                } else {
                    $redis = Redis::factory();
                    $user = $userModel->toArray();
                    $isLogin = true;
                    cacheUserByToken($user, $redis);
                }
            } else {
                $isLogin = true;
                $user = $cacheUser;
            }
        }
        $this->query["is_login"] = $isLogin;
        $this->query["user"] = $user;
    }

    protected function checkAuth()
    {
        if ($this->query["is_login"]) {
            return true;
        }
        throw AppException::factory(AppException::USER_NOT_LOGIN);
    }

    /**
     * 前置操作
     * @access protected
     * @param  string $method  前置操作方法名
     * @param  array  $options 调用参数 ['only'=>[...]] 或者['except'=>[...]]
     */
    protected function beforeAction($method, $options = [])
    {
        if (isset($options['only'])) {
            if (is_string($options['only'])) {
                $options['only'] = strtolower($options['only']);
                $options['only'] = explode(',', $options['only']);
            }
            foreach ($options['only'] as &$only) {
                $only = trim($only);
            }
            unset($only);
            if (!in_array($this->request->action(), $options['only'])) {
                return;
            }
        } elseif (isset($options['except'])) {
            if (is_string($options['except'])) {
                $options['except'] = strtolower($options['except']);
                $options['except'] = explode(',', $options['except']);
            }
            foreach ($options['except'] as &$except) {
                $except = trim($except);
            }
            unset($except);
            if (in_array($this->request->action(), $options['except'])) {
                return;
            }
        }
        call_user_func([$this, $method]);
    }
}