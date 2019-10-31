<?php
/**
 * Created by PhpStorm.
 * User: zhc
 * Date: 2019/10/22
 * Time: 11:07
 */

namespace Logic;

use think\Db;
class VisitorService
{

    /**
     * 访客留言立碑
     * @param $seller_id
     * @param $where
     * @return array
     */
    public  static  function  getCustomerServiceList($seller_id,$where=[],$page=1,$limit=10){
        $list = Db::name('visitor_service_log')->where('seller_id',$seller_id)
            ->where($where)->order('service_log_id', 'desc')->page($page,$limit)->select();
        return $list;
    }


    public  static  function  getCustomerServiceCount($seller_id,$where=[]){
        return Db::name('visitor_service_log')->where('seller_id',$seller_id)->where($where)->count();
    }

    public  static  function  addServiceLog($data){
        return  Db::name('visitor_service_log')->insertGetId($data);
    }

    public  static  function  setEndTimeEndId($visitor_id){
        //获取最新的记录
        $info= Db::name('visitor_service_log')->where('visitor_id',$visitor_id)->order('start_date desc')->find();
        if($info){
            return  Db::name('visitor_service_log')->where('vsid',$info['vsid'])->update([
                'end_date'=>date('Y-m-d H:i:s'),
                'connect_stauts'=>2,
            ]);
        }else{
            return  true;
        }

    }


}
