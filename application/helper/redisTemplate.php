<?php
/**
 * redis key 统一前缀
 */
define("REDIS_KEY_PREFIX", "wb_dianshang:");

/**
 * banner 列表
 */
define("REDIS_KEY_BANNER_LIST_BY_POSITION", REDIS_KEY_PREFIX . "bannerListByPosition:");

//缓存banner列表，有效期1小时
function cacheBannerByPosition(array $list, $position, \Redis $redis)
{
    $key = REDIS_KEY_BANNER_LIST_BY_POSITION . $position;
    $redis->setex($key, 3600, json_encode($list));
}

// 获取banner列表
function getBannerByPosition($position, \Redis $redis)
{
    $key = REDIS_KEY_BANNER_LIST_BY_POSITION . $position;
    $data = $redis->get($key);
    if ($data) {
        return json_decode($data, true);
    } else {
        return [];
    }
}

// 删除banner缓存
function deleteBannerByPosition($position, \Redis $redis)
{
    $key = REDIS_KEY_BANNER_LIST_BY_POSITION . $position;
    $redis->del($key);
}

/**
 * 企业微信access_token
 */
define("REDIS_KEY_WE_CHAT_WORK_ACCESS_TOKEN", REDIS_KEY_PREFIX . "weChatWorkAccessToken");

function getWeChatWorkAccessToken(\Redis $redis)
{
    return $redis->get(REDIS_KEY_WE_CHAT_WORK_ACCESS_TOKEN);
}

//缓存企业微信access_token
function setWeChatWorkAccessToken($accessToken, $expire, \Redis $redis)
{
    $redis->setex(REDIS_KEY_WE_CHAT_WORK_ACCESS_TOKEN, $expire, $accessToken);
}

/**
 * 缓存用户信息 token
 */
define("REDIS_KEY_USER_BY_TOKEN", REDIS_KEY_PREFIX . "userByToken:");
//缓存用户信息
function cacheUserByToken(array $user, \Redis $redis, $oldToken = "")
{
    $key = REDIS_KEY_USER_BY_TOKEN . $user["token"];

    $redis->hMSet($key, $user);
    $redis->expire($key, 259200);

    if ($oldToken != "") {
        $oldKey = REDIS_KEY_USER_BY_TOKEN . $oldToken;
        $redis->del($oldKey);
    }
}

//通过token获取用户信息
function getUserByToken($token, \Redis $redis)
{
    $key = REDIS_KEY_USER_BY_TOKEN . $token;
    return $redis->hGetAll($key);
}

/**
 * 用户表信息 user_id
 */
define("REDIS_KEY_USER_BY_ID", REDIS_KEY_PREFIX . "userById:");
//缓存用户信息，有效期3天
function cacheUserById(array $user, \Redis $redis)
{
    $key = REDIS_KEY_USER_BY_ID . $user["id"];

    $redis->hMSet($key, $user);
    $redis->expire($key, 259200);
}

//通过user_id获取用户信息
function getUserById($userId, \Redis $redis)
{
    $key = REDIS_KEY_USER_BY_ID . $userId;
    return $redis->hGetAll($key);
}

/**
 * user_info表信息 user_id
 */
define("REDIS_KEY_USER_INFO_BY_UID", REDIS_KEY_PREFIX . "userINfoByUId:");
//缓存用户详情，有效期3天
function cacheUserInfoById(array $userInfo, \Redis $redis)
{
    $key = REDIS_KEY_USER_INFO_BY_UID . $userInfo["u_id"];
    $redis->hMSet($key, $userInfo);
    $redis->expire($key, 259200);
}

//通过user_id获取用户信息
function getUserInfoById($userId, \Redis $redis)
{
    $key = REDIS_KEY_USER_INFO_BY_UID . $userId;
    return $redis->hGetAll($key);
}

/**
 * 用户小程序 sessionKey
 */
define("REDIS_KEY_MP_SESSION_KEY_ID", REDIS_KEY_PREFIX . "mpSessionKeyId:");
//缓存用户小程序 sessionKey
function setUserSessionKey($sessionKeyId, $sessionKey, \Redis $redis) {
    $key = REDIS_KEY_MP_SESSION_KEY_ID . $sessionKeyId;
    return $redis->setex($key, 10 * 86400, $sessionKey);
}

//获取用户小程序 sessionKey
function getUserSessionKey($sessionKeyId, \Redis $redis) {
    $key = REDIS_KEY_MP_SESSION_KEY_ID . $sessionKeyId;
    return $redis->get($key);
}

/**
 * 定制需求提交标记
 */
define("REDIS_KEY_DEMAND_SIGN", REDIS_KEY_PREFIX . "demandSign:");
// 纪录用户提交标记，有效期5秒
function addDemandSign($userId, \Redis $redis) {
    $key = REDIS_KEY_DEMAND_SIGN . $userId;
    $redis->set($key, 1);
}

// 获取用户提交标记
function getDemandSign($userId, \Redis $redis) {
    $key = REDIS_KEY_DEMAND_SIGN . $userId;
    return $redis->get($key);
}

