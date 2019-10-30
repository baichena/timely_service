<?php
/**
 * Created by PhpStorm.
 * User: zhc
 * Date: 2019/10/18
 * Time: 11:28
 */

namespace Logic;

use exception\LogicException;
use think\Db;
use think\facade\Log;
use Logic\SellerLogic;

class KefuLogic
{


    /**
     * 查找在线客服
     * @param $kefu_code  客服编码
     */
    public static function findKefuByCodeOnline($code)
    {
        return Db::name('kefu_info')->where('kefu_code', $code)->where('kefu_status', 1)->find();
    }

    /**
     * 查找激活并在线的客服
     * @param $kefu_code  客服编码
     */
    public static function getKefuByGroupId($group_id)
    {
        return Db::name('kefu_info')->where('group_id', $group_id)->where('kefu_status', 1)->where('online_status', 1)->select();
    }


    /**
     * 设置客服在线 离线
     * @param $uid  客服编码
     */
    public static function setKefuOnlineStatus($kefu_code, $fd, $status = 1)
    {
        if (!in_array($status, [0, 1, 2])) {
            throw new LogicException('客服状态错误', 8001);
        }
        $kefu_info = Db::name('kefu_info')->where('kefu_code', $kefu_code)->find();

        if (!$kefu_info) {
            throw new LogicException('客服不存在', 8002);
        }
        $update['online_status'] = $status;
        if ($status == 1) {
            $update['client_id'] = $fd;
        } else {
            $update['client_id'] = '';
        }
        $result = Db::name('kefu_info')->where('kefu_code', $kefu_code)->update($update);
        if ($result === false) {
            throw new LogicException('客服状态更新失败', 8003);
        }
        return array_merge($kefu_info, $update);

    }

    /**
     * 分配客服
     */
    public static function distributionKefu($data)
    {
        if (empty($data)) {
            throw new LogicException('参数缺失', 8001);
        }
        Log::record('分配参数' . json_encode($data));
        //指定分配
        $kefu_info = self::findKefu($data);
        return $kefu_info;
    }

    public static function findKefu($data)
    {

        $customer_info = Visitor::getCustomerInfoOnlineByCustomerId($data['visitor_id']);
        if (empty($customer_info)) {
            throw new LogicException('暂无游客登录信息', 8004);
        }
        $kefu_info = self::findKefuByCodeOnline($data['kefu_code']);
        if (empty($kefu_info)) {
            Log::record('客服不存在 或者 客服不在线 匹配客服');
            return ['code' => 201, 'data' => [
            ], 'msg' => '客服不在线'];

        }
        return ['code' => 200, 'data' => [
            'kefu_id' => $kefu_info['kefu_id'],
            'kefu_code' => 'KF_' . $kefu_info['kefu_code'],
            'kefu_name' => $kefu_info['kefu_name'],
            'kefu_avatar' => $kefu_info['kefu_avatar'],
            'kefu_client_id' => $kefu_info['client_id'],
        ], 'msg' => '客服正常服务'];

    }

}



