<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-11-23
 * Time: 17:20
 */

namespace app\common\service;

use app\common\AppException;
use app\common\enum\IsShowEnum;
use think\Db;

class GoodsService extends Base
{
    public function goodsList($classify, $pageNum, $pageSize)
    {
        $list = Db::name("goods")
            ->where("classify", $classify)
            ->where("is_show", IsShowEnum::YES)
            ->field("id,goods_name,image_url,price")
            ->order("sort", "asc")
            ->limit(($pageNum-1)*$pageSize, $pageSize)
            ->select();
        foreach ($list as $key=>$item) {
            $list[$key]["g_id"] = $item["id"];
            $list[$key]["price"] = (string) $item["price"];
            unset($list[$key]["id"]);
        }

        $returnData["list"] = $list;
        return $returnData;
    }

    public function goodsInfo($goodsId)
    {
        $info = Db::name("goods")
            ->where("id", $goodsId)
            ->find();
        if (empty($info)) {
            AppException::factory(AppException::COM_PARAMS_ERR);
        }

        if (empty($info["gallery"])) {
            $gallery = [];
        } else {
            $gallery = explode(",", $info["gallery"]);
        }
        if (empty($info["description"])) {
            $description = [];
        } else {
            $description = explode(",", $info["description"]);
        }
        $goodsAppraises = Db::name("goods_appraises")->alias("ga")
            ->leftJoin("user_info ui", "ga.u_id=ui.u_id")
            ->where("ga.g_id", $goodsId)
            ->field("ga.score,ga.message,ga.create_time,
            ui.nickname,ui.portrait")
            ->order("ga.id", "desc")
            ->limit(2)
            ->select();
        foreach ($goodsAppraises as $key => $goodsAppraise) {
            $goodsAppraises[$key]["create_time"] = date("Y-m-d H:i", $goodsAppraise["create_time"]);
        }
        $returnData = [
            "g_id" => $info["id"],
            "goods_name" => $info["goods_name"],
            "image_url" => $info["image_url"],
            "price" => (string) $info["price"],
            "gallery" => $gallery,
            "introduction" => $info["introduction"],
            "description" => $description,
            "appraises" => $goodsAppraises,
        ];
        return $returnData;
    }
}