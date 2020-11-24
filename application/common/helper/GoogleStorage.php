<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-03-09
 * Time: 22:29
 */

namespace app\common\helper;

use Google\Cloud\Storage\StorageClient;

class GoogleStorage
{
    private static $object = null;
    private static $google_storage_url = "https://storage.googleapis.com";

    private static function getObject()
    {
        if (self::$object == null) {
            $googleStorageConfig = config("account.google_storage");
            $config = [
                "keyFilePath" => $googleStorageConfig["key_file_path"],
                "projectId" => $googleStorageConfig["project_id"],
            ];
            self::$object = new StorageClient($config);
        }

        return self::$object;
    }

    /**
     * 上传文件
     * @param $localFilename string 本地文件路径
     * @param $cloudFilename string 上传文件路径
     * @return string
     */
    public static function upload($localFilename, $cloudFilename)
    {
        $storage = self::getObject();

        $bucketName = config("account.google_storage.bucket");
        $bucket = $storage->bucket($bucketName);

        $localFile = fopen($localFilename, "r");
        $options = [
            "predefinedAcl" => "publicRead",
            "name" => $cloudFilename,
        ];

        $bucket->upload($localFile,$options);

        return self::$google_storage_url . "/$bucketName/$cloudFilename";
    }
}