<?php

namespace app\admin\controller;


use app\admin\service\Admin as adminService;
use app\admin\service\AuthGroup as authGroupService;
use app\admin\service\AuthGroupAccess as authGroupAccessService;
use app\admin\service\AuthRule as authRuleService;
use think\Controller;

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
}