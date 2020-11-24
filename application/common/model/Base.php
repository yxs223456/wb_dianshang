<?php
/**
 * Created by PhpStorm.
 * User: yangxs
 * Date: 2018/9/18
 * Time: 16:56
 */

namespace app\common\model;

use think\facade\Config;
use think\Model;

class Base extends Model
{
    protected $autoWriteTimestamp = true;
    /**
     * 分页查询列表，一般用于admin模块
     * @param $requestMap
     * @param string $field
     * @param bool $extraCondition
     * @param null $alias
     * @param null $order
     * @return Base|\think\Paginator
     * @throws \think\exception\DbException
     */
    public function paginateList($requestMap,$field="",$extraCondition=false,$alias=null,$order=null) {
        if (empty($field)) {
            $field = "*";
        }
        if(!$extraCondition) {
            return $this->field($field)
                ->where(getMapFromRequest($requestMap["condition"]))
                ->order(isNullOrEmpty($order)
                    ? $this->getPk()." desc" : $order)
                ->paginate(Config::get("paginate.list_rows"),
                    false,["query"=>$requestMap["page"]]);
        } else {
            if(!isNullOrEmpty($alias)) {
                return $this->alias($alias)->field($field)->where(getMapFromRequest($requestMap["condition"]))->order(isNullOrEmpty($order) ? $this->getPk()." desc" : $order);
            } else {
                return $this->field($field)->where(getMapFromRequest($requestMap["condition"]))->order(isNullOrEmpty($order) ? $this->getPk()." desc" : $order);
            }
        }
    }

    public function findById($id)
    {
        return $this->where(static::getPk(), $id)->find();
    }

    public function saveByData($data)
    {
        $returnData = $data;
        $returnData[static::getPk()] = $this->insertGetId($data);
        return $returnData;
    }

    public function updateByIdAndData($id,$data)
    {
        return $this->isUpdate(true)->save($data, [static::getPk() => $id]);
    }

    public function updateByMapAndData(array $data, array $map)
    {
        return $this->isUpdate(true)->save($data, $map);
    }

    public function findByMap(array $map)
    {
        return $this->where($map)->find();
    }

    public function deleteById($id)
    {
        return $this->where(static::getPk(), $id)->delete();
    }
}