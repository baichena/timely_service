<?php
/**
 * Created by PhpStorm.
 * User: zhc
 * Date: 2019/10/29
 * Time: 18:17
 */

namespace app\index\controller;


use think\Controller;
use think\Db;
use think\facade\Config;
class Kefu extends  Base
{
        public  function  index(){
            $config= Config::pull('swoole_server');
            $port=$config['port'];
            $this->assign('port',$port);
           $this->assign('url',request()->domain().'/index/index/user?kefu_code='.session('kefu_code'));
            $this->assign('kefu_name',session('kefu_name'));
            $this->assign('kefu_code',session('kefu_code'));
            return $this->fetch();
        }
        public function  getQueue(){
             $kefu_code = input('param.kefu_code');
             $reception_status  = input('param.status',1);
             $queue = Db::name('visitor_queue')->where('kefu_code',$kefu_code)->where('reception_status',$reception_status)->select();
            return json(['code'=>200,'data'=>$queue,'msg'=>'操作成功']);

        }

    //获取客服聊天记录
    public function getUserChatLog()
    {
        $uid = input('param.uid');
        $kefu_code = input('param.kefu_code');
        if (!$uid || !$kefu_code ) {
            return '参数错误';
        }
        $sql = "SELECT * FROM  chat_log WHERE ( from_id = '{$kefu_code}' and  to_id ='{$uid}') or (from_id = '{$uid}' and  to_id ='{$kefu_code}') order by create_time";
        $list = Db::query($sql);
        if (empty($list)) return json(['code'=>200,'data'=>$list,'msg'=>'操作成功']);
        foreach ($list as $key => $item) {
            if (strpos($item['from_id'], 'KF_') === false) {
                $list[$key]['log'] = 'visitor';
            } else {
                $list[$key]['log'] = 'kefu';
            }
        }
        return json(['code'=>200,'data'=>$list,'msg'=>'操作成功']);

    }
}
