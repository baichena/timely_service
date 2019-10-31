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

    public static function updateQueueByCusomerID($visitor_id, $update)
    {
        return Db::name('visitor_queue')->where('visitor_id', $visitor_id)->update($update);
    }

    public  static  function  getQueueing($kefu_code){
        return Db::name('visitor_queue')->where('kefu_code',$kefu_code)->where('reception_status', 1)->select();
    }
    public  static  function  updateQueueingkefuClientid($kefu_code,$fd){
        return Db::name('visitor_queue')->where('kefu_code',$kefu_code)->where('reception_status', 1)->update(['kefu_client_id'=>$fd]);
    }
    public  static  function  setReceptionStatus($visitor_id,$status,$fd=0){
        return Db::name('visitor_queue')->where('visitor_id',$visitor_id)->update(['client_id'=>$fd,'reception_status'=>$status]);
    }

}
