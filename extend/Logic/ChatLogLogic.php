<?php
/**
 * Created by PhpStorm.
 * User: zhc
 * Date: 2019/10/28
 * Time: 17:20
 */

namespace Logic;


use think\Db;

class ChatLogLogic
{
  public static function addChatLog($data){
        return Db::name('chat_log')->insertGetId([
            'from_id' => $data['from_id'],
            'from_name' => $data['from_name'],
            'from_avatar' => $data['from_avatar'],
            'to_id' => $data['to_id'],
            'to_name' => $data['to_name'],
            'to_avatar' => $data['to_avatar'],
            'message' => htmlspecialchars($data['message']),
            'create_time' => date('Y-m-d H:i:s')
        ]);
  }
  public  static  function  updateSendStatus($logId,$status){
          return Db::name('chat_log')->where('log_id',$logId)->update([
              'send_status'=>$status,
              'create_time'=>date('Y-m-d H:i:s')
          ]);
  }
}
