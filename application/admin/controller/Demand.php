<?php

namespace app\admin\controller;

use app\common\enum\DemandIsCallEnum;
use app\common\model\DemandModel;

class Demand extends Common
{
    /**
     * 列表
     */
    public function list()
    {
        $requestMap = $this->convertRequestToMap();

        $demandModel = new DemandModel();

        $list = $demandModel->paginateList($requestMap);
        foreach ($list as $key => $item) {
            $list[$key]["is_call_msg"] = DemandIsCallEnum::getEnumDescByValue($item["is_call"]);
        }

        $this->assign('list', $list);

        $isCallEnum = DemandIsCallEnum::getAllList();
        $this->assign('isCallEnum', $isCallEnum);

        return $this->fetch();
    }

    /**
     * 修改处理状态
     */
    public function operateIsCall()
    {
        $id = input("id");
        $do = input("do", 0);

        $demandModel = new DemandModel();
        $demandModel->updateByIdAndData($id, ["is_call" => $do]);

        //跳转参数
        $page = input("page", 1);
        $isCall = input("is_call", -999);
        $this->redirect("list?page=$page&is_call=$isCall");
    }
}