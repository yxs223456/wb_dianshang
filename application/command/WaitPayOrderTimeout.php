<?php
/**
 * Created by PhpStorm.
 * User: yangxiushan
 * Date: 2020-11-26
 * Time: 15:45
 */

namespace app\command;

use app\common\Constant;
use app\common\enum\OrderStatusEnum;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\facade\Log;

class WaitPayOrderTimeout extends Command
{
    use CommandTrait;

    protected function configure()
    {
        // setName 设置命令行名称
        $this->setName('wb_dianshang:WaitPayOrderTimeout');
    }

    protected function execute(Input $input, Output $output)
    {
        sleep(60);
        try {
            $this->doWork();
        } catch (\Throwable $e) {
            $error = [
                "script" => self::class,
                "file" => $e->getFile(),
                "line" => $e->getLine(),
                "message" => $e->getMessage(),
            ];
            Log::write(json_encode($error), "error");
        }
    }

    private function doWork()
    {
        $timeoutOrders = Db::name("tmp_wait_pay_order")
            ->where("create_time", "<", time() - Constant::ORDER_TIMEOUT_TIME)
            ->column("o_id");

        Db::name("goods_order")
            ->whereIn("id", $timeoutOrders)
            ->where("order_status", OrderStatusEnum::WAIT_PAY)
            ->update([
                "order_status" => OrderStatusEnum::CANCEL,
                "update_time" => time(),
            ]);
        Db::name("tmp_wait_pay_order")->whereIn("o_id", $timeoutOrders)->delete();
    }
}