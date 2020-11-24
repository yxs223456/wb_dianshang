<?php

namespace app\admin\controller;

use app\common\helper\AliyunOss;
use app\common\helper\AwaS3;
use app\common\helper\GoogleStorage;
use taobao\AliOss;

class Upload extends Common {

    public function uploadImage()
    {
        $tempFile = $_FILES['file']['tmp_name'];

        $imageInfo = getimagesize($tempFile);
        $ext = image_type_to_extension($imageInfo[2], false);

        $fileName = config("app.app_name") . "/". date("Ymd") . "/".  uniqid(mt_rand()).".".$ext;

        $returnData['code'] = 200;
        $returnData['msg'] = '上传成功';

        switch (config("account.upload_driver")) {
            case "oss":
                $url = AliyunOss::putObject($fileName, file_get_contents($tempFile));
                break;
            case "s3":
                $url = AwaS3::upload($fileName, file_get_contents($tempFile));
                break;
            case "google_storage":
                $url = GoogleStorage::upload($tempFile, $fileName);
                break;
            default:
                $url = "/static/upload/". md5(uniqid(mt_rand(), true)).".".$ext;
                move_uploaded_file($_FILES['file']['tmp_name'], \Env::get("root_path")."public".$url);
                break;
        }

        $returnData['data']['url'] = $url;
        return json($returnData);
    }

    // 百度富文本编辑器上传oss
    public function uploadEditorToOss() {

        $tempFile = $_FILES['upfile']['tmp_name'];

        $fileName = md5(uniqid(mt_rand(), true)).".".strtolower(pathinfo($_FILES['upfile']['name'])["extension"]);

        $info = AliOss::uploadFile($tempFile,$fileName);

        $url = $info['info']['url'];
        $start = strrpos($url,"/");
        $imgName = substr($url, $start);

        $returnData = array(
            "state" => "SUCCESS",
            "url" => config('oss.Cname').$imgName,
            "title" => "",
            "original" => "",
            "type" => ".png",
            "size" => ''
        );

        return json($returnData);

    }

}