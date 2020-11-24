<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-09-24
 * Time: 15:25
 */
namespace app\admin\controller;

use app\common\enum\BannerPositionEnum;
use app\common\enum\IsShowEnum;
use app\common\helper\Redis;
use app\common\model\BannerModel;

class Banner extends Common
{
    public function index()
    {
        $bannerModel = new BannerModel();
        $requestMap = $this->convertRequestToMap();
        $list = $bannerModel->paginateList($requestMap, "", false, null, "sort asc");

        foreach ($list as $item) {
            $item["position_desc"] = BannerPositionEnum::getEnumDescByValue($item["position"]);
        }
        $this->assign("list", $list);

        $bannerIsShowEnum = IsShowEnum::getAllList();
        $this->assign("bannerIsShowEnum", $bannerIsShowEnum);

        return $this->fetch("list");
    }

    public function add()
    {
        $allPosition = BannerPositionEnum::getAllList();
        $this->assign("allPosition",$allPosition);

        return $this->fetch();
    }

    public function addPost()
    {
        $name = input('name', '');
        $imageUrl = input('image_url', '');
        $isShow = input("is_show", 0);
        $sort = (int) input("sort", 0);
        $position = (int) input("position",BannerPositionEnum::MP_HOME);

        $redis = Redis::factory();
        deleteBannerByPosition($position, $redis);

        $bannerModel = new BannerModel();
        $bannerModel->save([
            "name" => $name,
            "image_url" => $imageUrl,
            "is_show" => $isShow,
            "sort" => $sort,
            "position" => $position
        ]);

        $this->success("添加成功",url("index"));
    }

    public function edit()
    {
        $id = input("id");
        $bannerModel = new BannerModel();
        $info = $bannerModel->findById($id)->toArray();
        $this->assign("info", $info);

        $allPosition = BannerPositionEnum::getAllList();
        $this->assign("allPosition",$allPosition);

        return $this->fetch();
    }

    public function editPost()
    {
        $id = input("id");
        $name = input('name', '');
        $imageUrl = input('image_url', '');
        $sort = (int) input("sort", 0);

        $position = (int) input("position",BannerPositionEnum::MP_HOME);
        $redis = Redis::factory();
        deleteBannerByPosition($position, $redis);

        $bannerModel = new BannerModel();
        $bannerModel->updateByIdAndData($id, [
            "name" => $name,
            "image_url" => $imageUrl,
            "sort" => $sort,
        ]);

        $this->success("修改成功",url("index"));
    }

    public function operateIsShow()
    {
        $id = input("id");
        $isShow = input("do", 0);

        $position = (int) input("position",BannerPositionEnum::MP_HOME);
        $redis = Redis::factory();
        deleteBannerByPosition($position, $redis);

        $bannerModel = new BannerModel();
        $bannerModel->updateByIdAndData($id, ["is_show" => $isShow]);

        //跳转参数
        $page = input("page",1);

        $this->redirect("index?page=$page&is_show=".input("is_show"));
    }
}
