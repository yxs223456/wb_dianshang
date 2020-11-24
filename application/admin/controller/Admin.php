<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-04-29
 * Time: 16:34
 */

namespace app\admin\controller;

use app\common\Constant;
use think\Db;

class Admin extends Common
{
    public function convertRequestToWhereSql()
    {

        $whereSql = " is_valid = 1";
        $pageMap = [];

        $params = input("param.");

        foreach ($params as $key => $value) {

            if ($value == "-999"
                || isNullOrEmpty($value))
                continue;

            switch ($key) {

                case "username":
                    $whereSql .= " and a.username like '%$value%'";
                    break;

                case "keyword":
                    $whereSql .= " and (a.username like '%$value%' or a.real_name like '%$value%')";
                    break;

            }

            $pageMap[$key] = $value;
            $this->assign($key, $value);

        }
        $data["whereSql"] = $whereSql;
        $data["pageMap"] = $pageMap;

        return $data;

    }

    public function adminList()
    {
        $condition = $this->convertRequestToWhereSql();
        $list = $this->adminService->getListByCondition($condition);
        $this->assign('list', $list);

        return $this->fetch("adminList");
    }

    public function add()
    {
        $group = Db::name("auth_group")
            ->where("status",1)
            ->select();
        $this->assign("groups", $group);
        return $this->fetch();
    }

    public function addPost()
    {
        $username = trim(input("username"));
        $password = md5(trim(input("password")));
        $groupId = input("group_id");

        Db::name("admin")->insert([
            "username" => $username,
            "real_name" => $username,
            "password" => $password,
            "status" => 1,
            "group_id" => $groupId,
        ]);

        $this->success("添加成功",url("adminList"));
    }

    public function edit()
    {
        $id = input("id");
        $group = Db::name("auth_group")
            ->where("status",1)
            ->select();
        $this->assign("groups", $group);
        $info = $this->adminService->findById($id);
        $this->assign("info", $info);
        return $this->fetch();
    }

    public function editPost()
    {
        $id = input("id");

        $username = trim(input("username"));
        $password = md5(trim(input("password")));
        $groupId = input("group_id");
        $this->adminService->updateByIdAndData($id, [
            "username" => $username,
            "real_name" => $username,
            "password" => $password,
            "group_id" => $groupId,
        ]);
        $this->success("修改成功",url("adminList"));
    }

    /**
     * 普通管理员列表
     */
    public function commonAdminList()
    {
        $condition = $this->convertRequestToWhereSql();
        $condition["whereSql"] .= " and group_id = 6";
        $list = $this->adminService->getListByCondition($condition);
        $this->assign('list', $list);

        return $this->fetch("commonAdminList");
    }

    /**
     * 添加普通管理员页面
     */
    public function addCommonAdminPage()
    {
        return $this->fetch("addCommonAdminPage");
    }

    /**
     * 执行添加普通管理员
     */
    public function doAddCommonAdmin()
    {
        $username = trim(input("username"));
        $password = md5(trim(input("password")));
        $realName = trim(input("real_name"));

        $old = Db::name("admin")->where("username", $username)->find();
        if ($old) {
            $this->error('账号已存在');
        }

        Db::name("admin")->insert([
            "username" => $username,
            "real_name" => $realName,
            "password" => $password,
            "status" => 1,
            "group_id" => Constant::COMMON_ADMIN_GROUP_ID,
        ]);

        $this->success("添加成功",url("commonAdminList"));
    }

    /**
     * 修改普通管理员页面
     */
    public function editCommonAdminPage()
    {
        $id = input("id");
        $info = $this->adminService->findById($id);
        $this->assign("info", $info);
        return $this->fetch("editCommonAdminPage");
    }

    /**
     * 执行修改普通管理员
     */
    public function doEditCommonAdmin()
    {
        $id = input("id");
        $username = trim(input("username"));
        $realName = trim(input("real_name"));

        $old = Db::name("admin")
            ->where("username", $username)
            ->where("id", "<>", $id)
            ->find();
        if ($old) {
            $this->error('账号已存在');
        }

        $updateData = [
            "username" => $username,
            "real_name" => $realName,
        ];
        if (trim(input("password"))) {
            $updateData["password"] = md5(trim(input("password")));
        }

        $this->adminService->updateByIdAndData($id, $updateData);
        $this->success("修改成功",url("commonAdminList"));
    }

    /**
     * 删除普通管理员
     */
    public function deleteCommonAdmin()
    {
        $id = input("id");
        Db::name("admin")
            ->where("id", $id)
            ->update([
                "is_valid" => 0,
            ]);

        //跳转参数
        $page = input("page",1);
        $keyword = input('keyword', '');

        $this->redirect("commonAdminList?page=$page&keyword=$keyword");
    }
}