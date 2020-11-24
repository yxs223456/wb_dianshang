<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-09-25
 * Time: 11:25
 */
namespace app\admin\controller;

use app\common\enum\CertificateStatusEnum;
use app\common\helper\Redis;
use app\common\model\UserCertificationModel;
use think\Db;
use think\Exception;

class User extends Common
{
    /**
     * 认证用户列表
     */
    public function certificationList()
    {
        $userCertificationModel = new UserCertificationModel();
        $requestMap = $this->convertRequestToMap();
        if (isset($requestMap["condition"]["user_number"])) {
            $userNum = $requestMap["condition"]["user_number"];
            unset($requestMap["condition"]["user_number"]);
            if ($userNum) {
                $user = Db::name("user")->where("user_number", $userNum)->find();
                $requestMap["condition"]["u_id"] = $user["id"]??0;
            }
        }

        $list = $userCertificationModel->paginateList(
            $requestMap, "", false, null, "audit_status asc, audit_time desc");

        $uIds = [];
        foreach ($list as $item) {
            $uIds[] = $item["u_id"];
        }

        $users = [];
        if ($uIds) {
            $users = Db::name("user")->whereIn("id", $uIds)->select();
            $users = array_column($users, null, "id");
        }

        foreach ($list as $item) {
            $item["mobile_phone"] = $users[$item["u_id"]]["mobile_phone"] ?? "";
            $item["user_number"] = $users[$item["u_id"]]["user_number"] ?? "";
            $item["audit_status_desc"] = CertificateStatusEnum::getEnumDescByValue($item["audit_status"]);
            $item["audit_time_date"] = $item["audit_time"] ? date("Y-m-d H:i", $item["audit_time"]) : "--";
        }
        $this->assign("list", $list);

        $waitAuditStatusValue = CertificateStatusEnum::WAIT_AUDIT;
        $this->assign("waitAuditStatusValue", $waitAuditStatusValue);

        $certificateStatusEnum = CertificateStatusEnum::getAllList();
        unset($certificateStatusEnum[0]);
        $this->assign("certificateStatusEnum", $certificateStatusEnum);

        return $this->fetch("certificationList");
    }

    /**
     * 用户认证审核
     */
    public function auditCertification()
    {
        $id = input("id");
        $do = input("do", 0);
        $do = $do == 1 ? CertificateStatusEnum::SUCCESS : CertificateStatusEnum::FAIL;
        Db::startTrans();
        try {
            $certification = Db::name("user_certification")->where("id", $id)->lock(true)->find();
            $userInfo = Db::name("user_info")->where("u_id", $certification["u_id"])->find();
            if ($certification["audit_status"] != CertificateStatusEnum::WAIT_AUDIT) {
                throw new Exception("该条纪录已审核请勿重复操作");
            }

            Db::name("user_certification")->where("id", $id)->update([
                "audit_status" => $do,
                "audit_time" => time(),
            ]);
            if ($userInfo["certificate_status"] != CertificateStatusEnum::SUCCESS) {
                $userInfoUpdate["certificate_status"] = $do;
                if ($do == CertificateStatusEnum::SUCCESS) {
                    $userInfoUpdate["certificate_time"] = time();
                }
                Db::name("user_info")->where("id", $userInfo["id"])->update($userInfoUpdate);
            }
            Db::commit();

            deleteUserInfoDataByUId($userInfo["u_id"], Redis::factory());
        } catch (\Throwable $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        //跳转参数
        $page = input("page",1);
        $auditStatus = input("audit_status");
        $userNumber = input("user_number");
        $this->redirect("certificationList?page=$page&user_number=$userNumber&audit_status=$auditStatus");
    }
}