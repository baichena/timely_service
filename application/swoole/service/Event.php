<?php
/**
 * 信息操作类
 * User: zhc
 * Date: 2019/10/24
 * Time: 10:16
 */

namespace app\swoole\service;

use  Logic\VisitorService;
use  think\Db;
use  think\Exception;
use  think\facade\Log;
use  Logic\KefuLogic;
use  exception\LogicException;
use  Logic\ServiceLogic;
use exception\BaseException;
use Logic\QueueLogic;
use Logic\Visitor;
use Logic\ChatLogLogic;

class Event
{
    //统一在线
    public static $online = [];
    // 在线客服
    public static $kefu = [];
    //在线游客
    public static $visitor = [];

    //设置客服
    public static function setKefu($fd, $uid)
    {
        if (isset(self::$kefu[$uid])) {
            self::$kefu[$uid]['fd'] = $fd;
        } else {
            self::$kefu[$uid]['fd'] = $fd;
            self::$kefu[$uid]['visitor_fds'] = [];
        }
        return self::$kefu;
    }

    //设置游客
    public static function setVisitor($fd, $uid)
    {
        self::$visitor[$fd]['uid'] = $uid;
        self::$visitor[$fd]['bind_kefu_fd'] = '';
        self::$visitor[$fd]['bind_kefu_code'] = '';
        return self::$visitor;
    }

    /**
     * 客服登录
     * @param $fd  客户端标识
     * @param $data  请求数据
     */
    public static function kefuConnection($fd, $data, $server)
    {

        try {
            #1更新客服状态
            $kefu_code = ltrim($data['uid'], 'KF_');
            $info = KefuLogic::setKefuOnlineStatus($kefu_code, $fd);
            #2.设置客服信息
            self::$online[$fd] = $data['uid'];
            self::setKefu($fd, $data['uid']);
            //正在接管服务
            $list = QueueLogic::getQueueing($kefu_code);
            if ($list) {
                foreach ($list as $item) {
                    self::$kefu[$data['uid']]['visitor_fds'][$item['visitor_id']] = $item['client_id'];
                }
            }
            QueueLogic::updateQueueingkefuClientid($kefu_code, $fd);
            return self::reposon($fd, 200, '客服上线成功', [], 'kefu_online');
        } catch (BaseException $e) {
            throw new BaseException('客服上线错误', 401);
        }


    }

    /**
     * 游客登录
     * @param $fd  客户端标识
     * @param $data  请求数据
     */
    public static function visitorConnection($fd, $data, $server)
    {
        try {

            #1.游客队列中添加数据
            $queue = [
                'visitor_id' => $data['visitor_id'],
                'client_id' => $fd,
                'visitor_name' => $data['visitor_name'],
                'visitor_avatar' => $data['visitor_avatar'],
                'visitor_ip' => '127.0.0.1',
                'create_time' => date('Y-m-d H:i:s'),
                'reception_status' => 0,//等待客服接待状态
            ];
            QueueLogic::updateQueue($queue);
            #2.游客信息更新
            $data = [
                'visitor_id' => $data['visitor_id'],
                'client_id' => $fd,
                'visitor_name' => $data['visitor_name'],
                'visitor_avatar' => $data['visitor_avatar'],
                'visitor_ip' => '127.0.0.1',
                'create_time' => date('Y-m-d H:i:s'),
                'online_status' => 1
            ];
            Visitor::updateCustomer($data);
            //设置游客信息
            self::$online[$fd] = $data['visitor_id'];
            self::setVisitor($fd, $data['visitor_id']);
            return self::reposon($fd, 200, '上线成功', [], 'online');
        } catch (BaseException $e) {
            throw new BaseException('上线错误', 401);
        }
    }

    /**
     * 游客连接客服
     * @param $fd  客户端标识
     * @param $data  请求数据
     */
    public static function visitorToKefu($fd, $data, $server)
    {

        #1.分配客服
        $visitor = [
            'visitor_id' => $data['uid'],
            'visitor_name' => $data['name'],
            'visitor_avatar' => $data['avatar'],
            'visitor_ip' => '127.0.0.1',
            'client_id' => $fd,
            'kefu_code' => $data['kefu_code'],
        ];
        try {
            Db::startTrans();
            $kefu_info = KefuLogic::distributionKefu($visitor);
            Log::record('分配客服数据：' . json_encode($kefu_info));
            if ($kefu_info['code'] == 200) {
                #1.记录服务日志
                $logId = VisitorService::addServiceLog([
                    'visitor_id' => $visitor['visitor_id'],
                    'client_id' => $fd,
                    'visitor_name' => $visitor['visitor_name'],
                    'visitor_avatar' => $visitor['visitor_avatar'],
                    'visitor_ip' => $visitor['visitor_ip'],
                    'kefu_id' => $kefu_info['data']['kefu_id'],
                    'kefu_code' => ltrim($kefu_info['data']['kefu_code'], 'KF_'),
                    'start_date' => date('Y-m-d H:i:s'),
                ]);
                try {
                    if ($server->exist((int)$kefu_info['data']['kefu_client_id']) == false) {
                        Db::rollback();
                        return self::reposon($fd, 201, '客服不存在或者客服不在线', [], 'visitorToKefu');
                    }
                    $kefu_info['data']['log_id'] = $logId;
                    // 更新队列表
                    $update['reception_status'] = 1;//更改连接状态
                    $update['kefu_code'] = ltrim($kefu_info['data']['kefu_code'], 'KF_');
                    $update['kefu_client_id'] = $kefu_info['data']['kefu_client_id'];
                    QueueLogic::updateQueueByCusomerID($visitor['visitor_id'], $update);
                    #3.绑定客服和游客  bengan
                    self::$visitor[$fd]['bind_kefu_fd'] = $kefu_info['data']['kefu_client_id'];
                    self::$visitor[$fd]['bind_kefu_code'] = $kefu_info['data']['kefu_code'];
                    self::$kefu[$kefu_info['data']['kefu_code']]['visitor_fds'][$visitor['visitor_id']] = $fd;
                    #end
                    //推送客服  通知 来新游客
                    $message['visitor_id'] = $visitor['visitor_id'];
                    $resut = self::reposon((int)self::$visitor[$fd]['bind_kefu_fd'], 200, '游客上线', $message, 'userUpper');
                    $server->push($resut['fd'], $resut['data']);
                    Db::commit();
                    return self::reposon($fd, 200, $kefu_info['msg'], $kefu_info['data'], 'visitorToKefu');
                } catch (Exception $e) {
                    Db::rollback();
                    Log::info('分配客服数据错误信息：' . $e->getMessage());
                    //取消客服在线状态
                    KefuLogic::setKefuOnlineStatus(ltrim($kefu_info['data']['kefu_code'], 'KF_'), '', 0);
                    return self::reposon($fd, 401, '请重新尝试分配客服1', [], 'visitorToKefu');
                }

            } else if ($kefu_info['code'] == 201) {
                return self::reposon($fd, 201, '客服不存在或者客服不在线', [], 'visitorToKefu');
            }
            Db::commit();
        } catch (BaseException $e) {
            Db::rollback();
            Log::record('分配客服数据错误信息：' . $e->getMessage());
            return self::reposon($fd, 402, '请重新尝试分配客服2', [], 'visitorToKefu');
        }
        unset($customer, $kefu_info);
    }

    /**
     * 聊天
     * @param $fd  客户端标识
     * @param $data  请求数据
     */
    public static function message($fd, $data, $server)
    {
        Log::record('聊天信息[' . json_encode($data) . ']');
        Log::record('聊天信息[' . json_encode( self::$online) . ']');
        $uid = self::$online[$fd];
        try {
            //消息入库
            $chat_log_id = ChatLogLogic::addChatLog($data);
            $message = [
                'name' => $data['from_name'],
                'avatar' => $data['from_avatar'],
                'id' => $data['from_id'],
                'time' => date('Y-m-d H:i:s'),
                'message' => htmlspecialchars($data['message']),
                'log_id' => $chat_log_id
            ];
            if (strstr($uid, "KF_") !== false) {//客服发信息给游客

                if (!isset(self::$kefu[$uid]['visitor_fds'][$data['to_id']]) || ($server->exist((int)self::$kefu[$uid]['visitor_fds'][$data['to_id']])) == false) {
                    //更新聊天日志状态
                    ChatLogLogic::updateSendStatus($chat_log_id, 2);
                    return self::reposon($fd, 201, '游客离线', $message, 'message');
                } else {
                    $v_fd = self::$kefu[$uid]['visitor_fds'][$data['to_id']];
                    $resut = self::reposon((int)$v_fd, 200, '来新信息了', $message, 'chatMessage');
                    $server->push($resut['fd'], $resut['data']);
                }
            } else {  //游客发送给客服
                //获取 客服的fd
                if (!isset(self::$visitor[$fd]['bind_kefu_code']) || ($server->exist((int)self::$kefu[self::$visitor[$fd]['bind_kefu_code']]['fd']) == false)) {
                    //更新聊天日志状态
                    ChatLogLogic::updateSendStatus($chat_log_id, 2);
                    return self::reposon($fd, 201, '客服离线', $message, 'message');
                } else {
                    $kefu_fd = self::$kefu[self::$visitor[$fd]['bind_kefu_code']]['fd'];
                    $resut = self::reposon((int)$kefu_fd, 200, '来新信息了', $message, 'chatMessage');
                    $server->push($resut['fd'], $resut['data']);
                }
            }

        } catch (BaseException $e) {
            return self::reposon($fd, 400, '消息发送失败', $data['message'], 'message');
        }

        return self::reposon($fd, 200, '信息发送成功', $message, 'message');

    }

    /**
     * 客服主动退出
     * @param $fd  客户端标识
     * @param $data  请求数据
     */
    public static function kefuClose($fd, $data, $server)
    {

    }
    /**
     * 断开连接
     * @param $fd  客户端标识
     * @param $data  请求数据
     */
    public static function disconnect($fd, $server)
    {
        $uid = self::$online[$fd];
        if (strstr($uid, "KF_") !== false) { //客服退出
            //修改状态
            KefuLogic::setKefuOnlineStatus(ltrim($uid, 'KF_'), 0, 0);
            unset(self::$online[$fd], self::$kefu[$uid]);

        } else {//游客退出
            //更新状态
            Visitor::setOnline($uid, 0);
            //更新队列状态
            QueueLogic::setReceptionStatus($uid, 2, 0);
            //更新服务状态
            VisitorService::setEndTimeEndId($uid);
            //通知客服游客下线
            $message['visitor_id'] = $uid;
            $kufu_fd = self::$kefu[self::$visitor[$fd]['bind_kefu_code']]['fd'];
            $resut = self::reposon((int)$kufu_fd, 200, '游客下线', $message, 'diffClose');
            $server->push($resut['fd'], $resut['data']);
            if (isset(self::$online[$fd])) unset(self::$online[$fd]);
            if (isset(self::$visitor[$fd])) unset(self::$visitor[$fd]);
        }
        return true;
    }

    public static function reposon($fd, $code = 200, $msg = "操作成功", $data = '', $cmd = '')
    {
        $reposon['fd'] = $fd;
        $reposon['data'] = json_encode([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
            'cmd' => $cmd,
        ], JSON_UNESCAPED_UNICODE);

        return $reposon;
    }
}
