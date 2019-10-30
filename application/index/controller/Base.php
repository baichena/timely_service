<?php
/**
 * Created by PhpStorm.
 * User: zhc
 * Date: 2019/10/29
 * Time: 18:21
 */

namespace app\index\controller;


use think\App;
use think\Controller;

class Base extends Controller
{
   public  function  __construct(App $app = null)
   {
       parent::__construct($app);
       if( !session('kefu_name')){
           return  $this->redirect('login/login');
       }
   }
}
