<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-11-23
 * Time: 16:07
 */

namespace app\common\service;

use app\common\enum\BannerPositionEnum;
use app\common\enum\IsShowEnum;
use app\common\helper\Redis;
use think\Db;

class BannerService extends Base
{
    public function homeBanner()
    {
        $redis = Redis::factory();
        $bannerPosition = BannerPositionEnum::MP_HOME;
        $returnData = $this->getBannerListByPosition($bannerPosition, $redis);
        return $returnData;
    }

    public function getBannerListByPosition($position, $redis)
    {
        $list = getBannerByPosition($position, $redis);
        if (empty($list)) {
            $list = Db::name("banner")
                ->where("position", $position)
                ->where("is_show", IsShowEnum::YES)
                ->order("sort", "asc")
                ->field("image_url")
                ->select();
            cacheBannerByPosition($list, $position, $redis);
        }
        return $list;
    }
}