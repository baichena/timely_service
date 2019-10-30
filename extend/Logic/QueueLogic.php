<?php
/**
 * Created by PhpStorm.
 * User: zhc
 * Date: 2019/10/24
 * Time: 14:50
 */

namespace Logic;


use think\Db;
use think\facade\Log;

class QueueLogic
{
    public static function addQueue($data)
    {
        return Db::name('visitor_queue')->insert($data);
    }

    public static function updateQueue($data)
    {
        $info = Db::name('visitor_queue')->where('visitor_id', $data['visitor_id'])->find();
        if (!empty($info)) {
            return Db::name('visitor_queue')->where('visitor_id', $data['visitor_id'])->update($data);
        } else {
            return self::addQueue($data);
        }
    }

    public static function findFuwuNumByKefuCode($kefu_code)
    {
        return Db::name('visitor_queue')->where('reception_status', 1)->count();
    }

    public static function updateQueueByCusomerID($visitor_id, $update)
    {
        return Db::name('visitor_queue')->where('visitor_id', $visitor_id)->update($update);
    }

}
