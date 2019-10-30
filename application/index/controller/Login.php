<?php
/**
 * Created by PhpStorm.
 * User: zhc
 * Date: 2019/10/29
 * Time: 16:38
 */

namespace app\index\controller;


use think\Controller;
use think\Db;
use think\facade\Cache;
class Login extends Controller
{
    public function Login()
    {
        return $this->fetch();
    }

    public function Logining()
    {

        $name = input('post.name');
        $password = input('post.password');
        if (empty($name)) {
            return $this->error('请输入用户名');
        }
        if (empty($password)) {
            return $this->error('请输入密码');
        }
        $kefu_info = Db::name('kefu_info')->where('kefu_name', $name)->find();
        if ($kefu_info) {
            if (md5(trim($password)) != $kefu_info['kefu_password']) {
                return $this->error('密码错误');
            }
            session('kefu_name', $kefu_info['kefu_name']);
            session('kefu_code', $kefu_info['kefu_code']);
        } else {
            //同一个ip 限制注册3个账号
            $num = Cache::get(request()->ip());
            if ($num > 3) {
                return $this->error('同一ip限制注册三个账号');
            }

            //添加客服
            $kefu_data = [
                'kefu_code' => uniqid('kefu'),
                'kefu_name' => trim($name),
                'kefu_avatar' => '/static/common/images/kefu.jpg',
                'kefu_password' => md5(trim($password)),
                'kefu_status' => 1,
                'online_status' => 0,
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ];
            Db::name('kefu_info')->insertGetId($kefu_data);
            Db::commit();
            Cache::inc(request()->ip());
            session('kefu_name', $kefu_data['kefu_name']);
            session('kefu_code', $kefu_data['kefu_code']);

        }

      return  $this->redirect('kefu/index');
    }
    public  function  logout(){
        session('kefu_name',null);
        session('kefu_code', null);
        return  $this->redirect('login/login');
    }
}
