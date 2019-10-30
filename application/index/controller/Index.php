<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\facade\Config;
class Index extends Controller
{

    public function user()
    {

        $kefu_code = input('param.kefu_code');
        $kefu_info = Db::name('kefu_info')->where('kefu_code', $kefu_code)->find();
        if(!$kefu_info){
            return  '客服不存在';
        }
        $visitor_id = uniqid($kefu_info['kefu_id']);
        $visitor_name = '游客'.$visitor_id;
        $visitor_avatar ='/static/common/images/visitor.jpg';
        $config= Config::pull('swoole_server');
        $port=$config['port'];
        $this->assign('port',$port);
        $this->assign('code',$kefu_code);
        $this->assign('uid',$visitor_id);
        $this->assign('name',$visitor_name);
        $this->assign('avatar',$visitor_avatar);
        return $this->fetch();
    }


    //获取访客聊天记录
    public function getUserChatLog()
    {
        $uid = input('param.uid');
        $kefu_code = input('param.kefu_code');
        if (!$uid || !$kefu_code ) {
            return '参数错误';
        }
        $sql = "SELECT * FROM  chat_log WHERE ( from_id = '{$uid}' and  to_id ='{$kefu_code}') or (from_id = '{$kefu_code}' and  to_id ='{$uid}') order by create_time";
        $list = Db::query($sql);
        if (empty($list)) return json($list);
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
