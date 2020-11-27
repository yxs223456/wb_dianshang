<?php

namespace app\admin\controller;

use app\common\enum\GoodsClassifyEnum;
use app\common\enum\IsShowEnum;
use app\common\model\GoodsModel;
use app\common\model\GoodsScoreModel;
use think\Db;

class Goods extends Common {
    /**
     * 商品列表
     */
    public function list()
    {
        $requestMap = $this->convertRequestToMap();
        if (isset($requestMap["condition"]["goods_name"])) {
            $requestMap["condition"]["goods_name-like"] = $requestMap["condition"]["goods_name"];
            unset($requestMap["condition"]["goods_name"]);
        }

        $goodsModel = new GoodsModel();

        $list = $goodsModel->paginateList($requestMap, "", false, null, "sort asc");
        foreach ($list as $key=>$item) {
            $list[$key]["classify_msg"] = GoodsClassifyEnum::getEnumDescByValue($item["classify"]);
        }

        $this->assign('list',$list);

        $isShowEnum = IsShowEnum::getAllList();
        $this->assign('isShowEnum',$isShowEnum);
        $goodsClassifyEnum = GoodsClassifyEnum::getAllList();
        $this->assign('goodsClassifyEnum',$goodsClassifyEnum);

        return $this->fetch();
    }

    /**
     * 添加钻石页面
     */
    public function add()
    {
        $goodsClassifyEnum = GoodsClassifyEnum::getAllList();
        $this->assign("goodsClassifyEnum", $goodsClassifyEnum);

        return $this->fetch();
    }

    /**
     * 执行添加
     */
    public function addPost() {

        $param = input('post.');
        unset($param["file"]);

        $goodsModel = new GoodsModel();
        $goodsScoreModel = new GoodsScoreModel();
        Db::startTrans();
        try {
            $goodsModel->save($param);
            $goodsScoreModel->save([
                "g_id" => $goodsModel->id,
                "count" => 0,
                "total_score" => 0,
            ]);

            Db::commit();

        } catch (\Throwable $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success("添加成功",url("list"));
    }

    /**
     * 编辑页面
     */
    public function edit()
    {
        $input = input();
        $goodsModel = new GoodsModel();
        $goods = $goodsModel->findById($input["id"]);
        $goodsInfo = $goods->toArray();
        $goodsInfo["gallery"] = $goods->gallery ? $goods->gallery : "";
        $this->assign("info", $goodsInfo);

        $galleryArray = $goods->gallery ? explode(",", $goods->gallery) : [];
        $this->assign("galleryArray", $galleryArray);

        $descriptionArray = $goods->description ? explode(",", $goods->description) : [];
        $this->assign("descriptionArray", $descriptionArray);

        $goodsClassifyEnum = GoodsClassifyEnum::getAllList();
        $this->assign("goodsClassifyEnum", $goodsClassifyEnum);

        return $this->fetch();
    }

    /**
     * 修改钻石信息
     */
    public function editPost() {

        $param = input('post.');
        unset($param["file"]);
        $id = input("id");
        $goodsModel = new GoodsModel();

        Db::startTrans();
        try {
            $goodsModel->updateByIdAndData($id, $param);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->redirect("list");

    }

    /**
     * 上下架
     */
    public function operateIsShow()
    {
        $id = input("id");
        $isShow = input("do", 0);

        $bannerModel = new GoodsModel();

        $bannerModel->updateByIdAndData($id, ["is_show" => $isShow]);

        //跳转参数
        $page = input("page",1);
        $classify = input("classify",-999);
        $isShow = input("is_show",-999);
        $goodsName = input("goods_name", "");
        $this->redirect("list?page=$page&classify=$classify&is_show=$isShow&goods_name=$goodsName");
    }
}