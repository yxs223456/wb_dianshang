<?php
/**
 * Created by PhpStorm.
 * User: lichaoyang
 * Date: 2020/3/8
 * Time: 下午5:32
 */

namespace app\common\helper;

use Aws\S3\S3MultiRegionClient;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;

class AwaS3
{
    private static $bucketFilesystem = array();

    /**
     * 获取bucket对应的filesystem
     *
     * @param $bucket
     * @return Filesystem
     */
    public static function getFilesystem($bucket)
    {
        return isset(self::$bucketFilesystem[$bucket]) ? self::$bucketFilesystem[$bucket] : self::setFilesystem($bucket);
    }

    /**
     * 设置bucket并返回filesystem对象
     *
     * @param $bucket
     * @return Filesystem
     */
    private static function setFilesystem($bucket)
    {
        $data = config("account.s3");
        $key = $data["key_id"];
        $secret = $data["key_secret"];
        empty($bucket) && $bucket = $data["bucket"];

        $client = new S3MultiRegionClient([
            'credentials' => [
                'key' => $key,
                'secret' => $secret
            ],
            'version' => 'latest',
        ]);
        $adapter = new AwsS3Adapter($client, $bucket);
        $filesystem = new Filesystem($adapter);
        self::$bucketFilesystem[$bucket] = $filesystem;
        return $filesystem;
    }

    /**
     * 上传图片
     *
     * @param $filename
     * @param $fileContent
     * @return string
     */
    public static function upload($filename, $fileContent)
    {
        $s3Config = config("account.s3");
        $bucket = $s3Config["bucket"];
        $filesystem = self::getFilesystem($bucket);
        $result = $filesystem->put($filename, $fileContent, array('visibility' => "public"));

        if (!$result) {
            return "";
        }

        return $s3Config["base_url"] . $filename;
    }

    private function __clone()
    {

    }

}