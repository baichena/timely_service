<?php
/**
 * Created by PhpStorm.
 * User: zhc
 * Date: 2019/10/24
 * Time: 15:14
 */

namespace Logic;


use think\Db;

class Visitor
{
    public static function addCustomer($data)
    {
        return Db::name('visitor')->insert($data);

    }
    public  static  function updateCustomer($data){
        $info = Db::name('visitor')->where('visitor_id', $data['visitor_id'])->find();
        if(!empty($info)) {
          return  Db::name('visitor')->where('visitor_id', $data['visitor_id'])->update($data);
        }else {

            return self::addCustomer($data);
        }
    }

    public  static  function getCustomerInfoOnlineByCustomerId($visitor_id){
        $info =Db::name('visitor')->where('visitor_id', $visitor_id)->where('online_status',1)->find();
        return $info;

    }
    public  static  function getCustomerInfoCustomerId($visitor_id){
        $info =Db::name('visitor')->where('visitor_id', $visitor_id)->find();
        return  $info;

    }

}
