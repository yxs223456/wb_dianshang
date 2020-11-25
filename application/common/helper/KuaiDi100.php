<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-05-07
 * Time: 11:51
 */

namespace app\common\helper;

class KuaiDi100
{
    private function getAccountConfig()
    {
        return config("account.kuaidi100");
    }

    /**
     * @param $com string 查询的快递公司的编码
     * @param $num string 查询的快递单号
     * @return mixed
     */
    public function query($com, $num)
    {
        $account = $this->getAccountConfig();
        $appKey = $account["key"];
        $customer = $account["customer"];
        $postData["customer"] = $customer;
        $postData["param"] = json_encode([
            "com" => $com, //查询的快递公司的编码， 一律用小写字母
            "num" => $num, //查询的快递单号
        ]);
        $url = 'https://poll.kuaidi100.com/poll/query.do';
        $postData["sign"] = md5($postData["param"] . $appKey . $postData["customer"]);
        $postData["sign"] = strtoupper($postData["sign"]);
        $result = curl($url, "post", $postData, false, true);
        return $result;
    }
}