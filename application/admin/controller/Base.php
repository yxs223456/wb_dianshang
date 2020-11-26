<?php

namespace app\admin\controller;


use app\admin\service\Admin as adminService;
use app\admin\service\AuthGroup as authGroupService;
use app\admin\service\AuthGroupAccess as authGroupAccessService;
use app\admin\service\AuthRule as authRuleService;
use app\common\enum\OrderStatusEnum;
use think\Controller;
use think\Db;

class Base extends Controller {
    protected $adminService;
    protected $authGroupService;
    protected $authGroupAccessService;
    protected $authRuleService;
    protected $gearBoxService;

    public function __construct( AdminService $adminService,
                                 AuthGroupService $authGroupService,
                                 AuthGroupAccessService $authGroupAccessService,
                                 AuthRuleService $authRuleService){

        parent::__construct();

        $this->adminService = $adminService;
        $this->authGroupService = $authGroupService;
        $this->authGroupAccessService = $authGroupAccessService;
        $this->authRuleService = $authRuleService;
    }

    public function clockOrderInfo()
    {
        $orderData = Db::name("goods_order")
            ->whereIn("order_status", [OrderStatusEnum::WAIT_DELIVERY, OrderStatusEnum::WAIT_RECEIVE])
            ->column("order_status");
        $returnData = [
            "wait_delivery" => 0,
            "wait_receive" => 0,
        ];
        foreach ($orderData as $item) {
            if ($item == OrderStatusEnum::WAIT_DELIVERY) {
                $returnData["wait_delivery"] += 1;
            } else {
                $returnData["wait_receive"] += 1;
            }
        }
        return json($returnData);
    }
}